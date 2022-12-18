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

class mqttSend {

    private static $clientMQTT = null;
    private $client;

    //Если true - вести лог, критические события в лог попадают всегда
    private $logger;

    private function __construct(iConfigMQTT $configMQTT, $logger)
    {
        $this->logger = $logger;
        $this->client = new Mosquitto\Client($configMQTT->getID(). '_' .rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9));
        $this->client->onConnect([$this, 'onConnect']);
        $this->client->setCredentials($configMQTT->getUser(), $configMQTT->getPassword());
        $this->client->connect($configMQTT->getHost(), $configMQTT->getPort());
    }

    /**
     * Подключение к брокеру
     * @param false $logger
     * @return mqttSend|null
     */
    public static function connect($logger = false)
    {
        if (self::$clientMQTT == null) {
            $config = new mqttConfig();
            self::$clientMQTT = new mqttSend($config, $logger);
            unset($config);
        }
        return self::$clientMQTT;
    }

    public function onConnect($rc, $message)
    {
        if ($this->logger) {
            logger::writeLog('Подключился к MQTT брокеру. Код '.$rc.' - '.$message, loggerTypeMessage::NOTICE, loggerName::MQTT);
        }
    }

    public function publish($topic, $payload, $qos = 0, $retain = false) {
        $this->client->publish($topic, $payload, $qos, $retain);
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

    public function __construct($subscibe, $logger = false)
    {
        $this->subscibe = $subscibe;
        $this->logger = $logger;
        $this->updateSubscribeUnite();

        $config = new mqttConfig();
        $this->client = new Mosquitto\Client($config->getID(). '_' .rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9));

        $this->client->onConnect([$this, 'onConnect']);
        $this->client->onDisconnect([$this, 'onDisconnect']);
        $this->client->onMessage([$this, 'onMessage']);

        $this->client->setCredentials($config->getUser(), $config->getPassword());
    }

    private function updateSubscribeUnite() {
        $this->subscribeDevice = [];
        $this->deviceFormatPayload = [];
        $devices = managerDevices::getListDevices();
        foreach ($devices as $device) {
            if ($device->getNet()!=netDevice::ETHERNET_MQTT) continue;
            $topicStatus = $device->getTopicStat();
            if (empty($topicStatus)) continue;
            $this->subscribeDevice[$device->getDeviceID()] = $topicStatus;
            $this->deviceFormatPayload[$device->getDeviceID()] = $device->getDeviceFormatValue();
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
            logger::writeLog('Подключился к MQTT брокеру. Код '.$rc.' - '.$message, loggerTypeMessage::NOTICE, loggerName::MQTT);
        }

        if (!$rc) {
            //Подписки на топики
            $this->Subscribe();
        }
    }

    public function onDisconnect($rc)
    {
        if ($this->logger) {
            //logger::writeLog('Отключение от брокера. Код '.$rc, loggerTypeMessage::WARNING, loggerName::MQTT);
        }
    }

    private function Subscribe()
    {
        if (!$this->subscibe) return;
        foreach ($this->subscribeDevice as $subscribe) {
            if ($this->logger) {
                logger::writeLog('Подключение подписки '.$subscribe, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->subscribe($subscribe, 0);
        }
    }

    public function onMessage($message) {
        if ($this->logger) {
            logger::writeLog(sprintf('Пришло сообщение от mqtt: topic: %s, payload: %s', $message->topic, $message->payload),
                loggerTypeMessage::NOTICE,
                loggerName::MQTT);
        }
        $topic = trim($message->topic);
        if (empty($topic)) {
            logger::writeLog('Пришло пустое сообщение от mqtt', loggerTypeMessage::WARNING,loggerName::MQTT);
        }

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

            $deviceDataValue = $this->convertPayload($message->payload, $formatValueDevice);
            $deviceData = new deviceData($idDevice);
            $deviceData->setData($deviceDataValue['value'], time(), $deviceDataValue['valueNull'], $deviceDataValue['status']);
        }
    }

    private function convertPayload($payload, $format = formatValueDevice::NO_FORMAT) {

        $result = ['value' => 0.0, 'valueNull' => true, 'status' => 0];

        switch ($format) {
            case formatValueDevice::NO_FORMAT :
            case formatValueDevice::MQTT_TEMPERATURE :
            case formatValueDevice::MQTT_PRESSURE :
            case formatValueDevice::MQTT_HUMIDITY :
                $result['value'] = $payload;
                $result['valueNull'] = false;
                break;
            case formatValueDevice::MQTT_KEY_IN :
                $value = null;
                if (is_string($payload)) {
                    if (strtoupper($payload) == 'OFF' || strtoupper($payload) == 'FALSE' || $payload == '0') {$value = 0;}
                    if (strtoupper($payload) == 'ON' || strtoupper($payload) == 'TRUE' || $payload == '1') {$value = 1;}
                } elseif (is_numeric($payload)) {
                    if ($payload == 0) {$value = 0;}
                    elseif ($payload > 0) {$value = 1;}
                } elseif (is_bool($payload)) {
                    if ($payload) {$value = 1;}
                    else {$value = 0;}
                }
                if (!is_null($value)) {
                    $result['value'] = $value;
                    $result['valueNull'] = false;
                }
                break;
        }

        return $result;
    }

    public function loop()
    {
        $this->client->loop();
    }

}

class mqttTest extends mqttLoop
{

}