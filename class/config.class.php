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
        $DatabaseAccess = parse_ini_file(dirname(__FILE__) . '/../ini/access.ini');
        $this->db_host = $DatabaseAccess['dbhost'];
        $this->db_user = $DatabaseAccess['username'];
        $this->db_pwd = $DatabaseAccess['password'];
        $this->db_name = $DatabaseAccess['dbname'];
        $this->db_port = $DatabaseAccess['port'];
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