<?php

require_once(dirname(__FILE__)."/config.class.php");

class DBException extends Exception {
    public function __construct($mess) {
        parent::__construct($mess);
        error_log($this->__toString(), 0);
    }
}

class connectDBException extends DBException {

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

class sqlDataBase {
	
	private static $db = null;
	private $dbConnect;
	
	private function __construct() {
		//error_reporting(sqlConfig::err_rep);
		$this->dbConnect=new mysqli(sqlConfig::db_host, 
									sqlConfig::db_user, 
									sqlConfig::db_pwd, 
									sqlConfig::db_name, 
									3306); //порт
		if($this->dbConnect->connect_errno)
			$this->error(new connectDBException($this->dbConnect->connect_error));
	}

    /**
     * Подключиться к базе данных
     * @return null|sqlDataBase
     */
    public static function getConnect() {
		if (self::$db == null) self::$db = new sqlDataBase();
		return self::$db;
	}

    /**
     * Возвращает результат запроса в виде 2-х мерного ассоциативного массива
     * @param $query
     * @return array
     * @throws errorDBException
     */
    public function getAll($query) {
        $res = array();
        if ($resQ = $this->getRaw($query)) {
            while ($row = $resQ->fetch_assoc()) {
                $res[] = $row;
            }
            $resQ->free();
        }
        return $res;
    }

    /**
     * возвращает первую строку результата запроса в виде ассоциативного массива
     * @param $query
     * @return array
     * @throws errorDBException
     */
    public function getOne($query) {
        $row = array();
        if ($resQ = $this->getRaw($query)) {
            $row = $resQ->fetch_assoc();
            if (!is_array($row)) {
                $this->error(new otherDBException("Не могу результат запроса преобразовать в массив"));
            }
            $resQ->free();
        }
        return $row;
    }

    private function error($E) {
        //trigger_error($err_mes,E_USER_ERROR);
    	throw $E;
    }

    /**
     * Возразает "сырой" результат запроса SELECT
     * @param $query
     * @return bool|mysqli_result
     * @throws errorDBException
     */
    private function getRaw($query) {
        $res = $this->dbConnect->query($query, MYSQLI_USE_RESULT);
        if (!$res) {
        	$this->error(new querySelectDBException($this->dbConnect->error));
        }
        return $res;
	}

    public function __destruct() {
        if ($this->dbConnect) $this->dbConnect->close();
    }

}
?>
