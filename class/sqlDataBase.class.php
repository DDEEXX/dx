<?php

require_once(dirname(__FILE__) . '/config.class.php');
require_once(dirname(__FILE__) . '/lists.class.php');
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
        error_log($this->__toString());
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
        $txt = '<h1>Сайт ' . $_SERVER['SERVER_NAME'] . '</h1>';
        $txt .= '<h2>Не могу подключиться к базе данных.</h2>';
        $txt .= '<h3>' . $this->getMessage() . '</h3>';
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
        $txt = '<h1>Не могу получить данные из таблиц базы данных.</h1>';
        $txt .= '<h2>' . $this->getMessage() . '</h2>';
        return $txt;
    }
}

//class otherDBException extends DBException implements iDBException
//{
//    /**
//     * Возвращает описание прочих ошибок в виде html для вывода на странице
//     * @return string
//     */
//    public function getErrorInfoHTML()
//    {
//        $txt = '<h1>Ошибка при работе с базой данных</h1>';
//        $txt .= '<h2>' . $this->getMessage() . '</h2>';
//        return $txt;
//    }
//}

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
        $res = [];
        if ($resQ = self::getRaw($conn, $query)) {
            while ($row = $resQ->fetch_assoc()) {
                $res[] = $row;
            }
            $resQ->free();
        }
        return $res;
    }

    /**
     * Возвращает первую строку результата запроса в виде ассоциативного массива или null если ничего не выбрано
     * @param iSqlDataBase $conn
     * @param $query
     * @return array|null
     * @throws querySelectDBException
     */
    public static function getOne(iSqlDataBase $conn, $query)
    {
        $resQ = self::getRaw($conn, $query);
        return $resQ->fetch_assoc();
    }

    /**
     * Возвращает "сырой" результат запроса SELECT
     * @param iSqlDataBase $conn
     * @param $query
     * @return mixed
     * @throws querySelectDBException
     */
    private static function getRaw(iSqlDataBase $conn, $query)
    {
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
     * Получить список полей физ. устройств в виде ассоциативного массива в соответствии с отбором
     * @param Iterator|null $sel - отбор
     * @return array
     */
    static public function getListDevices(Iterator $sel = null)
    {
        $query = 'SELECT * FROM tdevice';
        return self::getListBD($query, $sel);
    }

    /** Получить поля устройства
     * @param $id
     * @return array|null
     */
    static public function getDeviceID($id) {
        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getConst. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }
        $deviceID = $con->getConnect()->real_escape_string($id);
        $query = 'SELECT * FROM tdevice WHERE DeviceID=' .$deviceID;
        try {
            $result = queryDataBase::getOne($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции DB::getDeviceID. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }
        return $result;
    }

    /**
     * Получить список модулей (логических устройств) в виде ассоциативного массива в соответствии с отбором
     * @param Iterator|null $sel - отбор
     * @return array
     */
    static public function getListUnits(Iterator $sel = null)
    {
        $query = 'SELECT *, a.Note NoteU, b.Note NoteD FROM tunits a LEFT JOIN tdevice b ON a.DeviceID = b.DeviceID LEFT JOIN tunitetype c ON a.UniteTypeID = c.UniteTypeID';
        return self::getListBD($query, $sel);
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
            return [];
        }

        $query = $con->getConnect()->real_escape_string($titleQuery);

            if ($sel instanceof selectOption) {

                $w = '';

                foreach ($sel as $key => $value) {

                    if (!empty($w)) {
                        $w = $w . ' AND';
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
                    $query = $query . ' WHERE' . $w;
                }
            }

        try {
            $aDevices = queryDataBase::getAll($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при выполнение запроса в DB::getListBD. ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
            return [];
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
        $query = 'SELECT * FROM tusers';
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
     * Получить список камер
     * @param Iterator|null $sel - отбор
     * @return array
     */
    static public function getListCameras(Iterator $sel = null)
    {
        $query = 'SELECT * FROM tcameras';
        return self::getListBD($query, $sel);
    }

    static public function updateTestDeviceCode(iDevice $device, $code, $updateTime) {
        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::updateTestDeviceCode. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return;
        }

        $deviceID = $device->getDeviceID();
        $query = "SELECT MAX(Date) m_date FROM tdevicetest WHERE DeviceID = $deviceID";
        try {
            $maxDate = queryDataBase::getOne($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции DB::getUserId. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return;
        }
        if (!is_null($maxDate['m_date'])) {
            $query = sprintf('SELECT * FROM tdevicetest WHERE DeviceID = %s AND Date = \'%s\'',
                $deviceID, $maxDate['m_date']);
            try {
                $testCode = queryDataBase::getOne($con, $query);
            } catch (querySelectDBException $e) {
                logger::writeLog('Ошибка в функции DB::getUserId. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                    loggerTypeMessage::FATAL, loggerName::ERROR);
                return;
            }
            $currentTimeTestCode = strtotime($testCode['Date']);
            if ($currentTimeTestCode>=$updateTime) { return; }
            $currentTestCode = (int)$testCode['Code'];
            if ($currentTestCode == $code) { return; }
        }

        $dateTestCode = date('Y-m-d H:i:s', $updateTime);
        $query = sprintf('INSERT INTO tdevicetest (Date, DeviceID, Code) VALUES (\'%s\', %s, %s)',
            $dateTestCode, $deviceID, $code);
        try {
            $result = queryDataBase::execute($con, $query);
            if (!$result) {
                logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                    loggerTypeMessage::ERROR, loggerName::ERROR);
            }
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при добавлении данных в базу данных. '.$e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

    }

    public static function getLastTestCode()
    {
        $query = '
            SELECT
                d.DeviceID,
                d.Note,
                test_code.Date,
                test_code.Code
            FROM
                tdevice d
            LEFT JOIN(
                SELECT
                    test.Date,
                    test.DeviceID,
                    test.Code
                FROM
                    tdevicetest test
                JOIN(
                    SELECT
                        DeviceID,
                        MAX(DATE) AS max_date
                    FROM
                        tdevicetest
                    GROUP BY
                        DeviceID
                ) tmax_date
            ON
                test.DeviceID = tmax_date.DeviceID AND test.Date = tmax_date.max_date
            ) test_code
            ON
                d.DeviceID = test_code.DeviceID
            ORDER BY
                d.DeviceID';

        $query = str_replace(PHP_EOL,'',$query);

        return self::getListBD($query);
    }

}


