<?php

namespace units2;

require_once(dirname(__FILE__) . '/list2.class.php');

interface iSqlConnect
{
    static function Connect();
}

class sqlConnect implements iSqlConnect {
    private static $db = null;

    private $dbConnect;

    /**
     * sqlDataBase constructor.
     * @param iConfigDB $configDB
     * @throws connectDBException
     */
    private function __construct(iConfigDB $configDB)
    {
        //error_reporting(sqlConfig::err_rep);
        $this->dbConnect = new mysqli($configDB->getHost(),
            $configDB->getUser(),
            $configDB->getPassword(),
            $configDB->getNameDB(),
            $configDB->getPort());
        $this->dbConnect->set_charset('utf8');
        if ($this->dbConnect->connect_errno)
            throw new connectDBException($this->dbConnect->connect_error);
    }

    /**
     * Получить соединение mysqli
     * @return mysqli
     */
    public function getConnect()
    {
        return $this->dbConnect;
    }

    /**
     * Подключиться к базе данных
     * @return null|sqlDataBase
     * @throws connectDBException
     */
    public static function Connect()
    {
        if (self::$db == null) {
            $config = new sqlConfig();
            self::$db = new sqlDataBase($config);
            unset($config);
        } else {
            if (!mysqli_ping(self::$db->getConnect())) {
                $config = new sqlConfig();
                self::$db = new sqlDataBase($config);
                unset($config);
            }
        }
        return self::$db;
    }

    public function __destruct()
    {
        if ($this->dbConnect) {
            try {
                $this->dbConnect->close();
            } finally {

            }
        }
    }
}

class DB
{
    static public function getUnits(iListFilter $filter)
    {
        $query = 'SELECT * FROM units u 
            LEFT JOIN devices d1 ON u.Device1 = d1.ID LEFT JOIN devices d2 ON u.Device2 = d2.ID 
            LEFT JOIN devices d3 ON u.Device3 = d3.ID LEFT JOIN devices d4 ON u.Device4 = d4.ID 
            LEFT JOIN devices d5 ON u.Device5 = d5.ID LEFT JOIN devices d6 ON u.Device6 = d6.ID';
    }
}