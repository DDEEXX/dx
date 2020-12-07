<?php

/**
 * Interface iConfigDB - возращает настройки подключения к базе данных mySQL
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
    public function getHost();

    public function getUser();

    public function getPassword();


    public function getPort();
}

/**
 * Class mqqtConfig - настройки подключиения к MQTT брокеру
 */
class mqqtConfig implements iConfigMQTT
{
    private $mqqt_host;
    private $mqqt_user;
    private $mqqt_pwd;
    private $mqqt_port;
    //private $err_rep = 32759; // -1, 0, E_ALL, 32759 as (E_ALL ^ E_NOTICE), etc...

    /**
     * sqlConfig constructor.
     */
    public function __construct()
    {
        $DatabaseAccess = parse_ini_file(dirname(__FILE__) . '/../ini/access.ini', TRUE);
        $this->mqqt_host = $DatabaseAccess['mqttbroker']['host'];
        $this->mqqt_user = $DatabaseAccess['mqttbroker']['user'];
        $this->mqqt_pwd  = $DatabaseAccess['mqttbroker']['password'];
        $this->mqqt_port = $DatabaseAccess['mqttbroker']['port'];
    }

    public function getHost()
    {
        return $this->mqqt_host;
    }

    public function getUser()
    {
        return $this->mqqt_user;
    }

    public function getPassword()
    {
        return $this->mqqt_pwd;
    }

    public function getPort()
    {
        return $this->mqqt_port;
    }
}