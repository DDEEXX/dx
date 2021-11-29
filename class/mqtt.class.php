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
require_once(dirname(__FILE__) . '/managerUnits.class.php');

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

    public function __construct($subscibe, $logger = false)
    {
        $this->subscibe = $subscibe;
        $this->logger = $logger;

        $config = new mqttConfig();
        $this->client = new Mosquitto\Client($config->getID(). '_' .rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9));

        $this->client->onConnect([$this, 'onConnect']);
        $this->client->onDisconnect([$this, 'onDisconnect']);
        $this->client->onMessage([$this, 'onMessage']);

        $this->client->setCredentials($config->getUser(), $config->getPassword());
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
            logger::writeLog('Отключение от брокера. Код '.$rc, loggerTypeMessage::WARNING, loggerName::MQTT);
        }
    }

    private function Subscribe()
    {
        if (!$this->subscibe) return;
        //Получить список всех модулей, подключенные устройства которых это MQTT клиенты и них есть подписки
        $listUnitMQTTLoop = managerUnits::getListUnitsMQTTTopicStatus();
        foreach ($listUnitMQTTLoop as $unit) {
            if ($this->logger) {
                logger::writeLog('Подключение подписки '.$unit, loggerTypeMessage::NOTICE, loggerName::MQTT);
            }
            $this->client->subscribe($unit, 0);
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

        $unitsID = managerUnits::getListUnitsMQTTTopicStatus();
        foreach ($unitsID as $id => $topicUnit) {

            if ($topic != $topicUnit) continue;

            $unit = managerUnits::getUnitID($id);
            if (is_null($unit)) {
                continue;
            }
            if ($this->logger) {
            logger::writeLog(sprintf('По топику: %s, найден модуль с ID: %s', $topic, $unit->getId()),
                loggerTypeMessage::NOTICE,
                loggerName::MQTT);
            }

            $unit->updateValueMQTT($message->payload);

        }
    }

    public function loop()
    {
        $this->client->loop();
    }

}

class mqttTest extends mqttLoop
{

}