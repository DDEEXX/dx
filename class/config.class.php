<?php

/**
 * Interface iConfigDB - возвращает настройки подключения к базе данных mySQL
 */
interface iConfigDB
{
    public function getHost();

    public function getUser();

    public function getPassword();

    public function getNameDB();

    public function getPort();
    //public function getErr();
}

/**
 * Class sqlConfig - настройки подключиения к базе данных
 */
class sqlConfig implements iConfigDB
{
    private $db_host;
    private $db_user;
    private $db_pwd;
    private $db_name;
    private $db_port;
    //private $err_rep = 32759; // -1, 0, E_ALL, 32759 as (E_ALL ^ E_NOTICE), etc...

    /**
     * sqlConfig constructor.
     */
    public function __construct()
    {
        $DatabaseAccess = parse_ini_file(dirname(__FILE__) . '/../ini/access.ini', TRUE);
        $this->db_host = $DatabaseAccess['database']['dbhost'];
        $this->db_user = $DatabaseAccess['database']['username'];
        $this->db_pwd  = $DatabaseAccess['database']['password'];
        $this->db_name = $DatabaseAccess['database']['dbname'];
        $this->db_port = $DatabaseAccess['database']['port'];
    }

    public function getHost()
    {
        return $this->db_host;
    }

    public function getUser()
    {
        return $this->db_user;
    }

    public function getPassword()
    {
        return $this->db_pwd;
    }

    public function getNameDB()
    {
        return $this->db_name;
    }

    public function getPort()
    {
        return $this->db_port;
    }
    //public function getErr() {return self::err_rep;}
}

/**
 * Interface iConfigDB - возращает настройки подключения к базе данных mySQL
 */
interface iConfigMQTT
{

    public function getID();

    public function getHost();

    public function getUser();

    public function getPassword();

    public function getPort();
}

/**
 * Class mqttConfig - настройки подключения к MQTT брокеру
 */
class mqttConfig implements iConfigMQTT
{
    private $mqtt_id;
    private $mqtt_host;
    private $mqtt_user;
    private $mqtt_pwd;
    private int $mqtt_port;
    //private $err_rep = 32759; // -1, 0, E_ALL, 32759 as (E_ALL ^ E_NOTICE), etc...

    /**
     * sqlConfig constructor.
     */
    public function __construct()
    {
        $DatabaseAccess = parse_ini_file(dirname(__FILE__) . '/../ini/access.ini', TRUE);
        $this->mqtt_id   = $DatabaseAccess['mqttbroker']['id'];
        $this->mqtt_host = $DatabaseAccess['mqttbroker']['host'];
        $this->mqtt_user = $DatabaseAccess['mqttbroker']['user'];
        $this->mqtt_pwd  = $DatabaseAccess['mqttbroker']['password'];
        $this->mqtt_port = (int)$DatabaseAccess['mqttbroker']['port'];
    }

    public function getID()
    {
        return $this->mqtt_id;
    }

    public function getHost()
    {
        return $this->mqtt_host;
    }

    public function getUser()
    {
        return $this->mqtt_user;
    }

    public function getPassword()
    {
        return $this->mqtt_pwd;
    }

    public function getPort(): int
    {
        return $this->mqtt_port;
    }
}