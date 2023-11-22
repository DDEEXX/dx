<?php /** @noinspection PhpUnusedAliasInspection */

/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.12.20
 * Time: 23:16
 */

use Mosquitto\Client;

require_once(dirname(__FILE__) . '/config.class.php');
require_once(dirname(__FILE__) . '/logger.class.php');
require_once(dirname(__FILE__) . '/lists.class.php');
require_once(dirname(__FILE__) . '/managerDevices.class.php');

class mqttPublish
{
    static public function publish($topic, $payload, $qos = 0, $retain = false)
    {
        $configMQTT = new mqttConfig();
        $client = new Mosquitto\Client(null, true);
        //$client->onLog('mqttPublish::log');
        $client->setCredentials($configMQTT->getUser(), $configMQTT->getPassword());
        $client->connect($configMQTT->getHost(), $configMQTT->getPort());
        $client->publish($topic, $payload, $qos, $retain);
        $client->disconnect();
    }

    static public function log($level, $str) {
//        logger::writeLog('MQTT Publish. Level: '.$level.',  '.$str,
//            loggerTypeMessage::NOTICE, loggerName::DEBUG);
    }
}

class mqttSend
{
    private static $clientMQTT = null;
    private $client;
    private $idClient;

    //Если true - вести лог, критические события в лог попадают всегда
    private $logger;

    private function __construct(iConfigMQTT $configMQTT, $clientNamePostfix, $logger)
    {
        $this->logger = $logger;
        $this->idClient = $configMQTT->getID() . '_' . $clientNamePostfix. rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        $this->client = new Mosquitto\Client($this->idClient);
        $this->client->onConnect([$this, 'onConnect']);
        $this->client->setCredentials($configMQTT->getUser(), $configMQTT->getPassword());
        $this->client->connect($configMQTT->getHost(), $configMQTT->getPort());
    }

    function __destruct() {
        $this->client->disconnect();
    }

    /**
     * Подключение к брокеру
     * @param false $logger
     * @return mqttSend|null
     */
    public static function connect($clientNamePostfix = '', $logger = false)
    {
        if (self::$clientMQTT == null) {
            $config = new mqttConfig();
            self::$clientMQTT = new mqttSend($config, $clientNamePostfix, $logger);
            unset($config);
        }
        return self::$clientMQTT;
    }

    public function onConnect($rc, $message)
    {
        if ($this->logger) {
            logger::writeLog('Подключился к MQTT брокеру. Status: '.$rc.' - '.$message.'. ID: '.$this->idClient,
                loggerTypeMessage::NOTICE, loggerName::MQTT);
        }
    }

    public function publish($topic, $payload, $qos = 0, $retain = false)
    {
        $id = $this->client->publish($topic, $payload, $qos, $retain);
        if ($this->logger) {
            logger::writeLog('Отправка. id: '.$id.'; topic: '.$topic.'; payload '.$payload, loggerTypeMessage::NOTICE, loggerName::MQTT);
        }
    }

}

class mqttLoop
{
    private $client;
    //если false, то не подключать подписки
    private $subscibe;
    //Если true - вести лог, критические события в лог попадают всегда
    private $logger;
    // массив: индекс - id устройства, значения - топики
    private $subscribeDevice;
    // массив: индекс - id устройства, значения - формат преобразования входящих сообщений
    private $deviceFormatPayload;
    // массив: индекс - id устройства, значения - истина - обновляем данные, ложь - записываем
    private $deviceDataUpdate;
    // массив: индекс - id устройства, значения - объект device
    private $devices;

    public function __construct($subscibe, $mqttGroup, $logger = false)
    {
        $this->subscibe = $subscibe;
        $this->logger = $logger;
        $this->updateSubscribeUnite($mqttGroup);

        $config = new mqttConfig();
        $this->client = new Mosquitto\Client($config->getID() . '_loop_' . $mqttGroup . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9));

        $this->client->onConnect([$this, 'onConnect']);
        $this->client->onDisconnect([$this, 'onDisconnect']);
        $this->client->onMessage([$this, 'onMessage']);

        $this->client->setCredentials($config->getUser(), $config->getPassword());
    }

    private function updateSubscribeUnite($mqttGroup)
    {
        $this->subscribeDevice = [];
        $this->deviceFormatPayload = [];
        $this->deviceDataUpdate = [];
        $this->devices = [];

        $sel = new selectOption();
        $sel->set('Disabled', 0);
        $sel->set('NetTypeID', netDevice::ETHERNET_MQTT);
        $sel->set('mqtt_group', $mqttGroup);
        $devices = managerDevices::getListDevices($sel);
        foreach ($devices as $device) {
            $devicePhysic = $device->getDevicePhysic();
            if ($devicePhysic instanceof iDevicePhysic && $devicePhysic instanceof iDevicePhysicMQTT) {
                $topicStatus = $devicePhysic->getTopicStat();
                if (empty($topicStatus)) continue;
                $this->subscribeDevice[$device->getDeviceID()] = $topicStatus;
                $this->deviceFormatPayload[$device->getDeviceID()] = $devicePhysic->getFormatValue();
                //Для входящих ключей значения обновляем, для всех остальных сенсоров записываем
                if (is_a($device, 'keyInSensorDevice')) {
                    $this->deviceDataUpdate[$device->getDeviceID()] = true;
                } else {
                    $this->deviceDataUpdate[$device->getDeviceID()] = false;
                }

                $this->devices[$device->getDeviceID()] = $device;
            }
        }
    }

    public function connect()
    {
        $config = new mqttConfig();
        $this->client->connect($config->getHost(), $config->getPort());
        unset($config);
    }

    public function disconnect()
    {
        if (is_null($this->client)) return;
        $this->client->disconnect();
    }

    public function onConnect($rc, $message)
    {
        if ($this->logger) {
            logger::writeLog('Подключился к MQTT брокеру. Код ' . $rc . ' - ' . $message, loggerTypeMessage::NOTICE, loggerName::MQTT);
        }

        if (!$rc) {
            //Подписки на топики
            $this->Subscribe();
        }
    }

    public function onDisconnect($rc)
    {
//        if ($this->logger) {
//            //logger::writeLog('Отключение от брокера. Код '.$rc, loggerTypeMessage::WARNING, loggerName::MQTT);
//        }
    }

    private function Subscribe()
    {
        if (!$this->subscibe) return;
        foreach ($this->subscribeDevice as $subscribe) {
            if ($this->logger) {
                logger::writeLog('Подключение подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->subscribe($subscribe, 0);
        }
    }

    public function onMessage($message)
    {
        if ($this->logger) {
            logger::writeLog(sprintf('Пришло сообщение от mqtt: topic: %s, payload: %s', $message->topic, $message->payload),
                loggerTypeMessage::NOTICE,
                loggerName::MQTT);
        }
        $topic = trim($message->topic);
        $idDevices = array_keys($this->subscribeDevice, $topic, true); //список id всех устройств с подпиской topic
        foreach ($idDevices as $idDevice) {

            $formatValueDevice = formatValueDevice::NO_FORMAT;
            if (array_key_exists($idDevice, $this->deviceFormatPayload)) {
                $formatValueDevice = $this->deviceFormatPayload[$idDevice];
            }

            if ($this->logger) {
                logger::writeLog(sprintf('По топику: %s, найдено устройство с ID: %s', $topic, $idDevice),
                    loggerTypeMessage::NOTICE,
                    loggerName::MQTT);
            }

            //новый механизм
            if (array_key_exists($idDevice, $this->devices)) {
                $devicePhysic = $this->devices[$idDevice]->getDevicePhysic();
                if ($devicePhysic->isValue()) {
                    $devicePhysic->setValue($message->payload, $idDevice);
                    continue;
                }
            }

            //старый механизм
            $updateData = false;
            if (array_key_exists($idDevice, $this->deviceDataUpdate)) {
                $updateData = $this->deviceDataUpdate[$idDevice];
            }

            if ($this->logger) {
                logger::writeLog(sprintf('$formatValueDevice %s', $formatValueDevice),
                    loggerTypeMessage::NOTICE,
                    loggerName::MQTT);
            }

            $deviceDataValue = $this->convertPayload($message->payload, $formatValueDevice);
            switch ($formatValueDevice) {
                case formatValueDevice::MQTT_KITCHEN_HOOD :
                case formatValueDevice::MQTT_GAS_SENSOR :
                    $device = managerDevices::getDevice($idDevice);
                    if (is_a($device, 'aSensorDevice')) {
                        $device->saveValue($deviceDataValue);
                    }
                    if ($this->logger) {
                        logger::writeLog(sprintf('save %s', $deviceDataValue),
                            loggerTypeMessage::NOTICE,
                            loggerName::MQTT);
                    }
                    break;
                default :
                    $deviceData = new deviceData($idDevice);
                    if ($updateData === true) {
                        if ($this->logger) {
                            logger::writeLog('update ' . json_encode($deviceDataValue),
                                loggerTypeMessage::NOTICE,
                                loggerName::MQTT);
                        }
                        $deviceData->updateData($deviceDataValue['value'], time(), $deviceDataValue['valueNull'], $deviceDataValue['status']);
                    } else {
                        if ($this->logger) {
                            logger::writeLog('set ' . json_encode($deviceDataValue),
                                loggerTypeMessage::NOTICE,
                                loggerName::MQTT);
                        }
                        $deviceData->setData($deviceDataValue['value'], time(), $deviceDataValue['valueNull'], $deviceDataValue['status']);
                    }
            }
        }
    }

    private function convertPayload($payload, $format = formatValueDevice::NO_FORMAT)
    {

        $result = ['value' => 0.0, 'valueNull' => true, 'status' => 0];

        switch ($format) {
            case formatValueDevice::NO_FORMAT :
            case formatValueDevice::MQTT_TEMPERATURE :
            case formatValueDevice::MQTT_PRESSURE :
            case formatValueDevice::MQTT_HUMIDITY :
                if (!is_null($payload) && $payload !== 'NULL') {
                    $result['value'] = $payload;
                    $result['valueNull'] = false;
                }
                break;
            case formatValueDevice::MQTT_KEY_IN :
                $value = null;
                if (is_string($payload)) {
                    if (strtoupper($payload) == 'OFF' || strtoupper($payload) == 'FALSE' || $payload == '0') {
                        $value = 0;
                    }
                    if (strtoupper($payload) == 'ON' || strtoupper($payload) == 'TRUE' || $payload == '1') {
                        $value = 1;
                    }
                } elseif (is_numeric($payload)) {
                    if ($payload == 0) {
                        $value = 0;
                    } elseif ($payload > 0) {
                        $value = 1;
                    }
                } elseif (is_bool($payload)) {
                    if ($payload) {
                        $value = 1;
                    } else {
                        $value = 0;
                    }
                }
                if (!is_null($value)) {
                    $result['value'] = $value;
                    $result['valueNull'] = false;
                }
                break;
            case formatValueDevice::MQTT_KEY_OUT :
                $value = null;
                $status = null;
                if (is_string($payload)) { //может прийти команда и статус
                    $p = explode(MQTT_CODE_SEPARATOR, $payload);
                    if (strtoupper($p[0]) == 'OFF' || strtoupper($p[0]) == 'FALSE' || $p[0] == '0') {
                        $value = 0;
                    } elseif (strtoupper($p[0]) == 'ON' || strtoupper($p[0]) == 'TRUE' || $p[0] == '1') {
                        $value = 1;
                    }
                    if (count($p) > 1) {
                        $status = $p[1];
                    }
                } elseif (is_int($payload)) {
                    $value = $payload == 0 ? 0 : 1;
                } elseif (is_bool($payload)) {
                    $value = $payload ? 1 : 0;
                }
                if (!is_null($value)) {
                    $result['value'] = $value;
                    $result['valueNull'] = false;
                    if (!is_null($status)) {
                        $result['status'] = $this->convertStatus($status);
                    }
                }
                break;
            case formatValueDevice::MQTT_SWITCH_WHD02 :
                $dataDecode = json_decode($payload, true);
                if (!is_null($dataDecode)) {
                    $state = managerDevices::checkDataValue('state', $dataDecode);
                    if (!is_null($state)) {
                        if ($state == 'ON') {
                            $result['value'] = 1;
                            $result['valueNull'] = false;
                        } elseif ($state == 'OFF') {
                            $result['value'] = 0;
                            $result['valueNull'] = false;
                        }
                    }
                }
                break;
            case formatValueDevice::MQTT_KITCHEN_HOOD :
            case formatValueDevice::MQTT_GAS_SENSOR :
                $result = $payload;
                break;
        }
        return $result;
    }

    /** Преобразует строковое представление статуса в его числовое значение
     * @param $status
     * @return int
     */
    private function convertStatus($status)
    {
        if (!is_string($status)) {
            return 0;
        }
        if (array_key_exists($status, statusKeyData::status)) {
            return statusKeyData::status[$status];
        }
        return 0;
    }

    public function loop()
    {
        $this->client->loop();
    }

}

class mqttTest
{
    private $client;
    private $host;
    private $port;
    //Если true - вести лог, критические события в лог попадают всегда
    private $logger;
    // массив: индекс - id устройства, значения - топики
    private $subscribeDevice;
    // массив: индекс - id устройства, значения - объект iDevice
    private $devices;

    public function __construct($logger = false)
    {
        $this->logger = $logger;

        $config = new mqttConfig();
        $this->host = $config->getHost();
        $this->port = $config->getPort();
        $this->client = new Mosquitto\Client($config->getID() . '_' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9));
        $this->client->setCredentials($config->getUser(), $config->getPassword());
        $this->client->onConnect([$this, 'onConnect']);
        $this->client->onDisconnect([$this, 'onDisconnect']);
        $this->client->onMessage([$this, 'onMessage']);
    }

    public function connect()
    {
        $this->client->connect($this->host, $this->port);
    }

    public function disconnect()
    {
        if (is_null($this->client)) return;
        $this->client->disconnect();
    }

    public function onConnect($rc, $message)
    {
        if ($this->logger) {
            logger::writeLog('Подключился к MQTT брокеру. Код ' . $rc . ' - ' . $message, loggerTypeMessage::NOTICE, loggerName::MQTT);
        }

        if (!$rc) {
            //Подписки на топики
            $this->subscribe();
        }
    }

    public function onDisconnect($rc)
    {
//        if ($this->logger) {
//            //logger::writeLog('Отключение от брокера. Код '.$rc, loggerTypeMessage::WARNING, loggerName::MQTT);
//        }
    }

    public function onMessage($message)
    {
        if ($this->logger) {
            logger::writeLog(sprintf('Пришло сообщение от mqtt: topic: %s, payload: %s', $message->topic, $message->payload),
                loggerTypeMessage::NOTICE,
                loggerName::MQTT);
        }
        $topic = trim($message->topic);
        if (empty($topic)) {
            logger::writeLog('Пришло пустое сообщение от mqtt', loggerTypeMessage::WARNING, loggerName::MQTT);
        }

        $idDevices = array_keys($this->subscribeDevice, $topic, true); //список id всех устройств с подпиской topic
        foreach ($idDevices as $idDevice) {
            if ($this->logger) {
                logger::writeLog(sprintf('По топику: %s, найдено устройство с ID: %s', $topic, $idDevice),
                    loggerTypeMessage::NOTICE,
                    loggerName::MQTT);
            }
            //$device = managerDevices::getDevice($idDevice);
            $device = $this->devices[$idDevice];
            if (is_null($device)) continue;
            $devicePhysic = $device->getDevicePhysic();
            //"сырой" результат тестирования -> код тестирования
            $testDeviceCode = $devicePhysic->formatTestPayload($message->payload, true);
            if (!is_null($testDeviceCode)) managerDevices::updateTestCode($device, $testDeviceCode, time());
        }
    }

    public function loop()
    {
        $this->client->loop();
    }

    /** Обновляет подписки из базы данных
     * @return void
     */
    public function updateSubscribe()
    {
        foreach ($this->subscribeDevice as $subscribe) {
            if ($this->logger) {
                logger::writeLog('Отключение от подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->unsubscribe($subscribe);
        }

        $this->subscribe();
    }

    private function subscribe()
    {
        $this->getSubscribeDevice();

        foreach ($this->subscribeDevice as $subscribe) {
            if ($this->logger) {
                logger::writeLog('Подключение подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->subscribe($subscribe, 0);
        }
    }

    private function getSubscribeDevice()
    {
        $this->subscribeDevice = [];
        $this->devices = [];

        $sel = new selectOption();
        $sel->set('NetTypeID', netDevice::ETHERNET_MQTT);
        $devices = managerDevices::getListDevices($sel);
        foreach ($devices as $device) {
            $devicePhysic = $device->getDevicePhysic();
            if ($devicePhysic instanceof iDevicePhysic && $devicePhysic instanceof iDevicePhysicMQTT) {
                $topicTest = $devicePhysic->getTopicTest();
                if (strlen($topicTest)) {
                    $this->subscribeDevice[$device->getDeviceID()] = $topicTest;
                }
            }
            $this->devices[$device->getDeviceID()] = $device;
        }
    }
}

class mqttAlarm
{
    private $client;
    private $host;
    private $port;
    //Если true - вести лог, критические события в лог попадают всегда
    private $logger;
    // массив: индекс - id устройства, значения - топики
    private $subscribeDevice;

    public function __construct($logger = false)
    {
        $this->logger = $logger;

        $config = new mqttConfig();
        $this->host = $config->getHost();
        $this->port = $config->getPort();
        $this->client = new Mosquitto\Client($config->getID() . '_' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9));
        $this->client->setCredentials($config->getUser(), $config->getPassword());
        $this->client->onConnect([$this, 'onConnect']);
        $this->client->onDisconnect([$this, 'onDisconnect']);
        $this->client->onMessage([$this, 'onMessage']);
    }

    public function connect()
    {
        $this->client->connect($this->host, $this->port);
    }

    public function disconnect()
    {
        if (is_null($this->client)) return;
        $this->client->disconnect();
    }

    public function onConnect($rc, $message)
    {
        if ($this->logger) {
            logger::writeLog('Подключился к MQTT брокеру. Код ' . $rc . ' - ' . $message, loggerTypeMessage::NOTICE, loggerName::MQTT);
        }

        if (!$rc) {
            //Подписки на топики
            $this->subscribe();
        }
    }

    public function onDisconnect($rc)
    {
//        if ($this->logger) {
//            //logger::writeLog('Отключение от брокера. Код '.$rc, loggerTypeMessage::WARNING, loggerName::MQTT);
//        }
    }

    public function onMessage($message)
    {
        if ($this->logger) {
            logger::writeLog(sprintf('Пришло сообщение от mqtt: topic: %s, payload: %s', $message->topic, $message->payload),
                loggerTypeMessage::NOTICE,
                loggerName::MQTT);
        }
        $topic = trim($message->topic);
        if (empty($topic)) {
            logger::writeLog('Пришло пустое сообщение от mqtt', loggerTypeMessage::WARNING, loggerName::MQTT);
        }

        $idDevices = array_keys($this->subscribeDevice, $topic, true); //список id всех устройств с подпиской topic
        foreach ($idDevices as $idDevice) {

            $device = managerDevices::getDevice($idDevice);

            if ($device instanceof iDeviceAlarm) {

                if ($this->logger) {
                    logger::writeLog(sprintf('По топику: %s, найдено устройство с ID: %s', $topic, $idDevice),
                        loggerTypeMessage::NOTICE,
                        loggerName::MQTT);
                }

                $device->onMessageAlarm($message->payload);
            }

        }
    }

    public function loop()
    {
        $this->client->loop();
    }

    /** Обновляет подписки из базы данных
     * @return void
     */
    public function updateSubscribe()
    {
        foreach ($this->subscribeDevice as $subscribe) {
            if ($this->logger) {
                logger::writeLog('Отключение от подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->unsubscribe($subscribe);
        }

        $this->subscribe();
    }

    private function subscribe()
    {
        $this->getSubscribeUnite();

        foreach ($this->subscribeDevice as $subscribe) {
            if ($this->logger) {
                logger::writeLog('Подключение подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->subscribe($subscribe, 0);
        }
    }

    private function getSubscribeUnite()
    {
        $this->subscribeDevice = [];

        $sel = new selectOption();
        $sel->set('Disabled', 0);
        $devices = managerDevices::getListDevices($sel);
        foreach ($devices as $device) {
            if ($device instanceof iDeviceAlarm) {
                $topicAlarm = $device->getTopicAlarm();
                if (strlen($topicAlarm)) {
                    $this->subscribeDevice[$device->getDeviceID()] = $topicAlarm;
                }
            }
        }
    }
}

class mqttAlice
{
    private $client;
    private $host;
    private $port;
    //Если true - вести лог, критические события в лог попадают всегда
    private $logger;
    // массив: индекс - id устройства, значения - топики
    private $subscribeSetDevice;
    // массив: индекс - id устройства, значения - объект iDevice
    private $subscribeStatDevice;
    // массив: индекс - id устройства, значения - объект iDevice
    private $devices;

    public function __construct($logger = false)
    {
        $this->logger = $logger;

        $config = new mqttConfig();
        $this->host = $config->getHost();
        $this->port = $config->getPort();
        $this->client = new Mosquitto\Client($config->getID() . '_alice_' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9));
        $this->client->setCredentials($config->getUser(), $config->getPassword());
        $this->client->onConnect([$this, 'onConnect']);
        $this->client->onDisconnect([$this, 'onDisconnect']);
        $this->client->onMessage([$this, 'onMessage']);
    }

    public function connect()
    {
        $this->client->connect($this->host, $this->port);
    }

    public function disconnect()
    {
        if (is_null($this->client)) return;
        $this->client->disconnect();
    }

    public function onConnect($rc, $message)
    {
        if ($this->logger) {
            logger::writeLog('Подключился к MQTT брокеру. Код ' . $rc . ' - ' . $message, loggerTypeMessage::NOTICE, loggerName::MQTT);
        }

        if (!$rc) {
            //Подписки на топики
            $this->subscribe();
        }
    }

    public function onDisconnect($rc)
    {
//        if ($this->logger) {
//            //logger::writeLog('Отключение от брокера. Код '.$rc, loggerTypeMessage::WARNING, loggerName::MQTT);
//        }
    }

    public function onMessage($message)
    {
        if ($this->logger) {
            logger::writeLog(sprintf('Пришло сообщение от mqtt: topic: %s, payload: %s', $message->topic, $message->payload),
                loggerTypeMessage::NOTICE,
                loggerName::MQTT);
        }
        $topic = trim($message->topic);
        if (empty($topic)) {
            logger::writeLog('Пришло сообщение c пустым топиком от mqtt', loggerTypeMessage::WARNING, loggerName::MQTT);
        }

        if (array_key_exists($topic, $this->subscribeSetDevice)) {
            $idDevices = $this->subscribeSetDevice[$topic];
            foreach ($idDevices as $idDevice) {
                if ($this->logger) {
                    logger::writeLog(sprintf('По топику: %s, найдено устройство с ID: %s', $topic, $idDevice),
                        loggerTypeMessage::NOTICE,
                        loggerName::MQTT);
                }

                $device = $this->devices[$idDevice];
                $_Alice = $device->getAlice();
                $mqtt =   array_filter($_Alice->mqtt, function($v) use($topic) {return $v->topic == $topic;});
                foreach ($mqtt as $value) {
                    if (is_null($value->formater)) continue;
                    $formatValue = $value->formater->convert($message->payload);
                    if ($value->typeTopic == typeTopic::SET) {
                        $device->setData($formatValue);
                        if ($this->logger) {
                            logger::writeLog(sprintf('На устройство с ID: %s отправлено %s', $idDevice, $formatValue),
                                loggerTypeMessage::NOTICE,
                                loggerName::MQTT);
                        }
                    }
                }
            }
        } else
        {
            $idDevices = array_keys($this->subscribeStatDevice, $topic, true); //список id всех устройств с подпиской topic
            foreach ($idDevices as $idDevice) {
                if ($this->logger) {
                    logger::writeLog(sprintf('По топику: %s, найдено устройство с ID: %s', $topic, $idDevice),
                        loggerTypeMessage::NOTICE,
                        loggerName::MQTT);
                }
                $device = $this->devices[$idDevice];
                $_Alice = $device->getAlice();
                $_Alice->sentStatus($message->payload);
            }
        }
    }

    public function loop()
    {
        $this->client->loop();
    }

    public function updateSubscribe()
    {
        foreach ($this->subscribeSetDevice as $subscribe=>$value) {
            if ($this->logger) {
                logger::writeLog('Отключение от подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->unsubscribe($subscribe);
        }
        foreach ($this->subscribeStatDevice as $subscribe) {
            if ($this->logger) {
                logger::writeLog('Отключение от подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->unsubscribe($subscribe);
        }

        $this->subscribe();
    }

    private function subscribe()
    {
        $this->getSubscribeDevice();

        foreach ($this->subscribeSetDevice as $subscribe=>$value) {
            if ($this->logger) {
                logger::writeLog('Подключение подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->subscribe($subscribe, 0);
        }
        foreach ($this->subscribeStatDevice as $subscribe) {
            if ($this->logger) {
                logger::writeLog('Подключение подписки ' . $subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->subscribe($subscribe, 0);
        }
    }

    private function getSubscribeDevice()
    {
        $this->subscribeSetDevice = [];
        $this->devices = [];

        $sel = new selectOption();
        $sel->set('NetTypeID', netDevice::ETHERNET_MQTT);
        $sel->set('Alice', 1);
        $devices = managerDevices::getListDevices($sel);
        foreach ($devices as $device) {
            if (is_null($device) || is_null($device->getAlice())) continue;
            //подписка на топики приходящие от Алисы
            $_Alice = $device->getAlice();
            foreach ($_Alice->mqtt as $mqtt) {
                if (strlen($mqtt->topic) && $mqtt->typeTopic == typeTopic::SET) {
                    $this->subscribeSetDevice[$mqtt->topic][] = $device->getDeviceID();
                }
            }
            //подписка на топики от устройств о своем состоянии
            $devicePhysic = $device->getDevicePhysic();
            if ($devicePhysic instanceof iDevicePhysic && $devicePhysic instanceof iDevicePhysicMQTT) {
                $topicStatus = $devicePhysic->getTopicStat();
                if (!empty($topicStatus)) {
                    $this->subscribeStatDevice[$device->getDeviceID()] = $topicStatus;
                }
            };
            $this->devices[$device->getDeviceID()] = $device;
        }
    }
}

