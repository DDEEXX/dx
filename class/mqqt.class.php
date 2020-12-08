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

class mqqt
{
    private static $mqqtClient = null;
    private $client;

    private function __construct(iConfigMQTT $configMQQT)
    {
        $this->client = new Mosquitto\Client("dxhome");

        $this->client->onConnect([$this, 'onConnect']);
        $this->client->onMessage([$this, 'onMessage']);

        $this->client->setCredentials($configMQQT->getUser(), $configMQQT->getPassword());
        $this->client->connect($configMQQT->getHost(), $configMQQT->getPort());
    }

    public static function Connect()
    {
        if (self::$mqqtClient == null) {
            $config = new mqqtConfig();
            self::$mqqtClient = new mqqt($config);
            unset($config);
        }
        return self::$mqqtClient;
    }

    public function onConnect()
    {
        logger::writeLog('Подключился к MQQT брокеру', loggerTypeMessage::NOTICE,loggerName::ACCESS);
        $this->client->subscribe('bath/store/cellar/humidity', 0);
    }

    function onMessage($message) {
        logger::writeLog(sprintf("Пришло сообщение от mqqt: topic: %s, payload: %s", $message->topic, $message->payload));
    }

    public function loop()
    {
        $this->client->loop();
    }

}