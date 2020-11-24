<?php

require_once(dirname(__FILE__) . "/config.class.php");
require_once(dirname(__FILE__) . "/lists.class.php");
require_once(dirname(__FILE__) . '/logger.class.php');

interface iSqlDataBase
{
    public function getConnect();
}

interface iDBException
{
    public function getErrorInfoHTML();
}

class DBException extends Exception
{
    public function __construct($mess)
    {
        parent::__construct($mess);
        error_log($this->__toString(), 0);
    }
}

class connectDBException extends DBException implements iDBException
{

    /**
     * Возвращает описание ошибки подключения к базе в виде html для вывода на странице
     * @return string
     */
    public function getErrorInfoHTML()
    {
        $txt = "<h1>Сайт " . $_SERVER['SERVER_NAME'] . "</h1>";
        $txt .= "<h2>Не могу подключиться к базе даных.</h2>";
        $txt .= "<h3>" . $this->GetMessage() . "</h3>";
        return $txt;
    }
}

class querySelectDBException extends DBException implements iDBException
{
    /**
     * Возвращает описание ошибки выполнения SELECT запроса в виде html для вывода на странице
     * @return string
     */
    public function getErrorInfoHTML()
    {
        $txt = "<h1>Не могу получить данные из таблиц базы данных.</h1>";
        $txt .= "<h2>" . $this->GetMessage() . "</h2>";
        return $txt;
    }
}

class otherDBException extends DBException implements iDBException
{
    /**
     * Возвращает описание прочих ошибок виде html для вывода на странице
     * @return string
     */
    public function getErrorInfoHTML()
    {
        $txt = "<h1>Ошибка при работе с базой данных</h1>";
        $txt .= "<h2>" . $this->GetMessage() . "</h2>";
        return $txt;
    }
}

class sqlDataBase implements iSqlDataBase
{

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
        if ($this->dbConnect->connect_errno)
            throw new connectDBException($this->dbConnect->connect_error);
    }

    /**
     * Получить соединение mysqli
     * @return mixed|mysqli
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
        }
        return self::$db;
    }

    public function __destruct()
    {
        if ($this->dbConnect) $this->dbConnect->close();
    }

}

class queryDataBase
{

    /**
     * Запросы типа INSERT и UPDATE
     * @param iSqlDataBase $conn
     * @param $query
     * @return bool
     * @throws querySelectDBException
     */
    public static function execute(iSqlDataBase $conn, $query)
    {
        /** @noinspection PhpUndefinedMethodInspection */
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
    public static function getAll(iSqlDataBase $conn, $query)
    {
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
    public static function getOne(iSqlDataBase $conn, $query)
    {
        $row = null;
        if ($resQ = self::getRaw($conn, $query)) {
            $row = $resQ->fetch_assoc();
            /**
             * if (!is_array($row)) {
             * throw new otherDBException("Не могу результат запроса преобразовать в массив");
             * }
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
    private static function getRaw(iSqlDataBase $conn, $query)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $res = $conn->getConnect()->query($query, MYSQLI_USE_RESULT);
        if (!$res) {
            throw new querySelectDBException($conn->getConnect()->error);
        }
        return $res;
    }

}

class DB
{

    /**
     * Получить список физ. устройств в виде ассоциативного массива в соответствии с отбором
     * @param Iterator|null $sel - отбор
     * @return array
     */
    static public function getListDevices(Iterator $sel = null)
    {

        //$query = "SELECT * FROM tdevice";
        $query = "SELECT * FROM tdevice left join tdevicemodel ON tdevice.modelID = tdevicemodel.modelID";
        $listDevices = self::getListBD($query, $sel);
        return $listDevices;

    }

    /**
     * Получить список модулей (лигических устройств) в виде ассоциативного массива в соответствии с отбором
     * @param Iterator|null $sel - отбор
     * @return array
     */
    static public function getListUnits(Iterator $sel = null)
    {
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
     */
    static function getListBD($titleQuery = '', Iterator $sel = null)
    {

        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка подключения к базе данных в DB::getListBD. ' . $e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
            return array();
        }

        $query = $con->getConnect()->real_escape_string($titleQuery);

        if (!is_null($sel)) {
            if ($sel instanceof selectOption) {

                $w = "";

                foreach ($sel as $key => $value) {

                    if (!empty($w)) {
                        $w = $w . " AND";
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

        try {
            $aDevices = queryDataBase::getAll($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при выполнии запроса в DB::getListBD. ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
            return array();
        }

        return $aDevices;

    }

    /**
     * Получить значение константы по имени
     * @param $name
     * @return array|mixed|null
     */
    static public function getConst($name)
    {

        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getConst. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }

        $name = $con->getConnect()->real_escape_string($name);
        $query = "SELECT Type, ValueInt, ValueDec, ValueTxt FROM tConst WHERE Name='$name' LIMIT 1";
        try {
            $result = queryDataBase::getOne($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции DB::getConst. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }

        if (!empty($result['Type'])) {
            switch ($result['Type']) {
                case 1 :
                    $result = $result['ValueInt'];
                    break;
                case 2 :
                    $result = $result['ValueDec'];
                    break;
                case 3 :
                    $result = $result['ValueTxt'];
                    break;
            }
        }
        else {
            $result = null;
        }
        return $result;
    }

    /**
     * Получить последнне записсаное в базе значение $value
     * @param iUnit $unit
     * @param null $value
     * @return array|null
     */
    static public function getLastValueUnit(iUnit $unit, $value = null)
    {

        $uniteID = $unit->getId();
        /** @noinspection PhpUndefinedMethodInspection */
        $nameTabValue = 'tvalue_' . $unit->getValueTable();
        if (is_null($value)) {
            $query = 'SELECT Date, Value FROM ' . $nameTabValue . ' WHERE UnitID="' . $uniteID . '" ORDER BY ValueID DESC LIMIT 1';
        }
        else {
            $query = 'SELECT Date, Value FROM ' . $nameTabValue . ' WHERE UnitID="' . $uniteID . '" AND Value ="' . $value . '" ORDER BY ValueID DESC LIMIT 1';
        }

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::getOne($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getLastValueUnit. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции DB::getLastValueUnit. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        }

        return $result;

    }

    /**
     * @param $unit
     * @return array|null
     */
    static public function getLastStatusKeyJournal($unit)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $uniteID = $unit->getId();

        $query = 'SELECT Date, Status FROM tjournalkey WHERE UnitID="' . $uniteID . '" ORDER BY JournalKeyID DESC LIMIT 1';

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::getOne($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getLastStatusKeyJournal. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции DB::getLastStatusKeyJournal. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        }

        return $result;
    }

    /**
     * Получить режим работы модуля OFF|ON|AUTO
     * @param iUnit $unit
     * @return mixed
     */
    static public function getModeUnit(iUnit $unit)
    {

        $uniteID = $unit->getId();

        $query = 'SELECT Mode FROM tunits WHERE UnitID="' . $uniteID . '"';

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::getOne($con, $query);
            $result = $result['Mode'];
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getLastValueUnit. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = 0;
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции DB::getLastValueUnit. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = 0;
        }

        return $result;

    }

    static public function getUserId($id) {

        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getUserId. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }

        $id = $con->getConnect()->real_escape_string($id);
        $query = "SELECT * FROM tusers WHERE id='$id'  LIMIT 1";
        try {
            $result = queryDataBase::getOne($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции DB::getUserId. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }

        return $result;

    }

    static public function getUserPassword($password) {

        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getUserHash. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }
        $query = "SELECT * FROM tusers";
        try {
            $result = queryDataBase::getAll($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции DB::getUserHash. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }

        foreach ($result as $value) {
            $hash = $value['Password'];
            if ( password_verify($password, $hash) ) {
                return $value;
            }
        }

        return null;

    }

    static public function userLastActive($user, $time, $onlyOnline = false) {
        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::userLastActive. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }
        $time = $con->getConnect()->real_escape_string($time);
        $userID = $user['ID'];
        $query = "UPDATE tusers SET online='$time', lastActive='$time' WHERE id='$userID'";
        if ($onlyOnline) {
            $query = "UPDATE tusers SET online='$time' WHERE id='$userID'";
        }
        try {
            queryDataBase::execute($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при обновлении последней активности пользователя. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
        }

    }

    /**
     * Получить список модулей (лигических устройств) в виде ассоциативного массива в соответствии с отбором
     * @param Iterator|null $sel - отбор
     * @return array
     */
    static public function getListCameras(Iterator $sel = null)
    {
        $query = 'SELECT * FROM tcameras';
        $listUnits = self::getListBD($query, $sel);
        return $listUnits;
    }


}


