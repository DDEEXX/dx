<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.12.20
 * Time: 23:16
 */

use Mosquitto\Client;

require_once(dirname(__FILE__) . "/config.class.php");

class mqqt
{

    private static $mqqtClient = null;

    private function __construct(iConfigMQTT $configMQQT)
    {
        $client = new Mosquitto\Client("dxhome");
        $client->setCredentials($configMQQT->getUser(), $configMQQT->getPassword());
        $client->connect($configMQQT->getHost(), $configMQQT->getPort());

        return $client;

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


}