<?php

require_once(dirname(__FILE__)."/config.class.php");
require_once(dirname(__FILE__)."/lists.class.php");

interface iSqlDataBase {
    public function getConnect();
}
interface iDBException {
    public function getErrorInfoHTML();
}

class DBException extends Exception {
    public function __construct($mess) {
        parent::__construct($mess);
        error_log($this->__toString(), 0);
    }
}

class connectDBException extends DBException implements iDBException {

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

class querySelectDBException extends DBException implements iDBException {
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

class otherDBException extends DBException implements iDBException {
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

    /**
     * sqlDataBase constructor.
     * @param iConfigDB $configDB
     * @throws connectDBException
     */
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
     * @return mixed|mysqli
     */
    public function getConnect() {
        return $this->dbConnect;
    }

    /**
     * Подключиться к базе данных
     * @return null|sqlDataBase
     * @throws connectDBException
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
     * Запросы типа INSERT и UPDATE
     * @param iSqlDataBase $conn
     * @param $query
     * @return bool
     * @throws querySelectDBException
     */
    public static function execute(iSqlDataBase $conn, $query)
    {
        $res = $conn->getConnect()->query($query, MYSQLI_USE_RESULT);
        if (!$res) {
            throw new querySelectDBException($conn->getConnect()->error);
        }
        return true;
    }

    /**
     * Возвращает результат запроса в виде 2-х мерного ассоциативного массива
     * @param iSqlDataBase $conn
     * @param $query
     * @return array
     * @throws querySelectDBException
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
     * возвращает первую строку результата запроса в виде ассоциативного массива или null если ничего не выбрано
     * @param iSqlDataBase $conn
     * @param $query
     * @return array|null
     * @throws querySelectDBException
     */
    public static function getOne(iSqlDataBase $conn, $query) {
        $row = array();
        if ($resQ = self::getRaw($conn, $query)) {
            $row = $resQ->fetch_assoc();
            /**
            if (!is_array($row)) {
                throw new otherDBException("Не могу результат запроса преобразовать в массив");
            }
            */
            $resQ->free();
        }
        return $row;
    }

    /**
     * Возразает "сырой" результат запроса SELECT
     * @param iSqlDataBase $conn
     * @param $query
     * @return mixed
     * @throws querySelectDBException
     */
    private static function getRaw(iSqlDataBase $conn, $query) {
        $res = $conn->getConnect()->query($query, MYSQLI_USE_RESULT);
        if (!$res) {
            throw new querySelectDBException($conn->getConnect()->error);
        }
        return $res;
    }

}

class DB {

    /**
     * Получить список физ. устройств в виде ассоциативного массива в соответствии с отбором
     * @param Iterator|null $sel - отбор
     * @return array
     * @throws connectDBException
     * @throws querySelectDBException
     */
    static public function getListDevices(Iterator $sel = null){

        $query = "SELECT * FROM tdevice";
        $listDevices = self::getListBD($query, $sel);
        return $listDevices;

    }

    /**
     * Получить список модулей (лигических устройств) в виде ассоциативного массива в соответствии с отбором
     * @param Iterator|null $sel - отбор
     * @return array
     * @throws connectDBException
     * @throws querySelectDBException
     */
    static public function getListUnits(Iterator $sel = null){

        //$query = "SELECT a.*, b.* FROM tunits a LEFT JOIN tdevice b ON a.DeviceID = b.DeviceID ";
        $query = 'SELECT *, a.Note NoteU, b.Note NoteD FROM tunits a LEFT JOIN tdevice b ON a.DeviceID = b.DeviceID';
        $listUnits = self::getListBD($query, $sel);
        return $listUnits;

    }

    /**
     * Получить список полей в виде ассоциативного массива
     * в соответствии с шапкой запроса и отбором
     * @param string $titleQuery - шапка запроса
     * @param Iterator|null $sel - отбор в виде объекта итератора
     * @return array
     * @throws connectDBException
     * @throws querySelectDBException
     */
    static function getListBD($titleQuery = '', Iterator $sel = null){

        $con = sqlDataBase::Connect();

        $query = $con->getConnect()->real_escape_string($titleQuery);

        if (!is_null($sel)) {
            if ($sel instanceof selectOption) {

                $w = "";

                foreach ($sel as $key => $value) {

                    if (!empty($w)) {
                        $w = $w." AND";
                    }

                    $realValue = $con->getConnect()->real_escape_string($value);
                    if (is_int($value)) {
                        $w = $w . " $key = $realValue";
                    }
                    else {
                        $w = $w . " $key = '$realValue'";
                    }

                }

                if (!empty($w)) {
                    $query = $query . " WHERE" . $w;
                }
            }
        }

        $aDevices = queryDataBase::getAll($con, $query);

        return $aDevices;

    }

}
?>
