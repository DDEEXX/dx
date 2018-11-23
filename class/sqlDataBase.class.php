<?php

require_once(dirname(__FILE__)."/config.class.php");

interface iSqlDataBase {

    public function getConnect();

}

class DBException extends Exception {
    public function __construct($mess) {
        parent::__construct($mess);
        error_log($this->__toString(), 0);
    }
}

class connectDBException extends DBException {

    /**
     * connectDBException constructor.
     */
    public function __construct($mess)
    {
        parent::__construct($mess);
    }

    /**
     * Возвращает описание ошибки подключения к базе в виде html для вывода на странице
     * @return string
     */
    public function getErrorInfoHTML() {
        $txt = "<h1>Сайт " . $_SERVER['SERVER_NAME'] . "</h1>";
        $txt .= "<h2>Не могу подключиться к базе даных.</h2>";
        $txt .= "<h3>" . $this->GetMessage() . "</h3>";
        return $txt;
    }
}

class querySelectDBException extends DBException {
    /**
     * Возвращает описание ошибки выполнения SELECT запроса в виде html для вывода на странице
     * @return string
     */
    public function getErrorInfoHTML() {
        $txt = "<h1>Не могу получить данные из таблиц базы данных.</h1>";
        $txt .= "<h2>".$this->GetMessage()."</h2>";
        return $txt;
    }
}

class otherDBException extends DBException {
    /**
     * Возвращает описание прочих ошибок виде html для вывода на странице
     * @return string
     */
    public function getErrorInfoHTML() {
        $txt = "<h1>Ошибка при работе с базой данных</h1>";
        $txt .= "<h2>".$this->GetMessage()."</h2>";
        return $txt;
    }
}

class sqlDataBase implements iSqlDataBase {
	
	private static $db = null;
	private $dbConnect;

    private function __construct(iConfigDB $configDB) {
		//error_reporting(sqlConfig::err_rep);
		$this->dbConnect=new mysqli($configDB->getHost(),
                                    $configDB->getUser(),
                                    $configDB->getPassword(),
                                    $configDB->getNameDB(),
                                    $configDB->getPort());
		if($this->dbConnect->connect_errno)
            throw new connectDBException($this->dbConnect->connect_error);
	}

    /**
     * Получить соединение mysqli
     * @return mysqli
     */
    public function getConnect() {
        return $this->dbConnect;
    }

    /**
     * Подключиться к базе данных
     * @return null|sqlDataBase
     */
    public static function Connect() {
		if (self::$db == null) {
		    $config = new sqlConfig();
		    self::$db = new sqlDataBase($config);
            unset($config);
        }
		return self::$db;
	}

    public function __destruct() {
        if ($this->dbConnect) $this->dbConnect->close();
    }

}

class queryDataBase {

    /**
     * Возвращает результат запроса в виде 2-х мерного ассоциативного массива
     * @param iSqlDataBase $conn
     * @param $query
     * @return array
     * @throws errorDBException
     */
    public static function getAll(iSqlDataBase $conn, $query) {
        $res = array();
        if ($resQ = self::getRaw($conn, $query)) {
            while ($row = $resQ->fetch_assoc()) {
                $res[] = $row;
            }
            $resQ->free();
        }
        return $res;
    }

    /**
     * возвращает первую строку результата запроса в виде ассоциативного массива
     * @param iSqlDataBase $conn
     * @param $query
     * @return array
     * @throws errorDBException
     */
    public static function getOne(iSqlDataBase $conn, $query) {
        $row = array();
        if ($resQ = self::getRaw($conn, $query)) {
            $row = $resQ->fetch_assoc();
            if (!is_array($row)) {
                self::error(new otherDBException("Не могу результат запроса преобразовать в массив"));
            }
            $resQ->free();
        }
        return $row;
    }

    /**
     * Возразает "сырой" результат запроса SELECT
     * @param iSqlDataBase $conn
     * @param $query
     * @return bool|mysqli_result
     * @throws errorDBException
     */
    private static function getRaw(iSqlDataBase $conn, $query) {
        $res = $conn->getConnect()->query($query, MYSQLI_USE_RESULT);
        if (!$res) {
            self::error(new querySelectDBException($conn->getConnect()->error));
        }
        return $res;
    }

    private static function error($E) {
        throw $E;
    }

}

?>
