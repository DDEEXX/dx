<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.12.20
 * Time: 23:16
 */

use Mosquitto\Client;

require_once(dirname(__FILE__) . "/config.class.php");
require_once(dirname(__FILE__) . '/logger.class.php');
require_once(dirname(__FILE__) . '/managerUnits.class.php');

class mqqt
{
    private static $mqqtClient = null;
    private $client;

    //если false, то не подключать подписки
    private $subscibe;

    //Если true - вести лог, критические события в лог попадают всегда
    private $logger;

    private function __construct(iConfigMQTT $configMQQT, $subscibe, $logger)
    {
        $this->subscibe = $subscibe;
        $this->logger = $logger;
        $this->client = new Mosquitto\Client($configMQQT->getID());

        $this->client->onConnect([$this, 'onConnect']);
        $this->client->onMessage([$this, 'onMessage']);

        $this->client->setCredentials($configMQQT->getUser(), $configMQQT->getPassword());
        $this->client->connect($configMQQT->getHost(), $configMQQT->getPort());
    }

    public static function Connect($subscibe = false, $logger = false)
    {
        if (self::$mqqtClient == null) {
            $config = new mqqtConfig();
            self::$mqqtClient = new mqqt($config, $subscibe, $logger);
            unset($config);
        }
        return self::$mqqtClient;
    }

    public function onConnect()
    {
        logger::writeLog('Подключился к MQQT брокеру', loggerTypeMessage::NOTICE,loggerName::MQQT);
        //Подписки на топики
        $this->Subscribe();
    }

    private function Subscribe()
    {
        if (!$this->subscibe) return;
        //Получить список всех модулей, подключенные устройства которых это MQQT клиенты и них есть подписки
        $listUnitMQQTLoop = managerUnits::getListUnitsMQQTTopicStatus(0);
        foreach ($listUnitMQQTLoop as $unit) {
            $this->client->subscribe($unit, 0);
        }
    }

    function onMessage($message) {
        if ($this->logger) {
            logger::writeLog(sprintf("Пришло сообщение от mqqt: topic: %s, payload: %s", $message->topic, $message->payload),
                loggerTypeMessage::NOTICE,
                loggerName::MQQT);
        }
        $topic = trim($message->topic);
        if (empty($topic)) {
            logger::writeLog("Пришло пустое сообщение от mqqt", loggerTypeMessage::WARNING,loggerName::MQQT);
        }
        $unitsID = managerUnits::getUnitStatusTopic($topic);
        foreach ($unitsID as $id) {
            $unit = managerUnits::getUnitID($id);
            if (is_null($unit)) {
                continue;
            }
            if ($this->logger) {
            logger::writeLog(sprintf("По топику: %s, найден модуль с ID: %s", $topic, $unit->getId()),
                loggerTypeMessage::NOTICE,
                loggerName::MQQT);
            }
            $value = self::convertPayload($message->payload);
            $unit->updateValue($value, statusKey::OUTSIDE);
        }
    }

    public function loop()
    {
        $this->client->loop();
    }

    public function publish($topic, $payload, $qos = 0, $retain = false) {
        $this->client->publish($topic, $payload, $qos, $retain);
    }

    /**
     * Преобразовывает аргумент в 0 или 1 (пока такая заплатка!!!)
     * @param $payload
     * @return int
     */
    private static function convertPayload($payload) {

        if (empty($payload)) {return 0;}

        if (is_string($payload)) {
            if (strtoupper($payload) == 'OFF' || strtoupper($payload) == 'FALSE' || $payload == '0') {return 0;}
            if (strtoupper($payload) == 'ON' || strtoupper($payload) == 'TRUE' || $payload == '1') {return 1;}
        }
        if (is_int($payload)) {
            if ($payload == 0) {return 0;}
            else {return 1;}
        }
        if (is_bool($payload)) {
            if ($payload) {return 1;}
            else {return 0;}
        }

        return 0;
    }

}