<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:13
 */

require_once(dirname(__FILE__) . "/sqlDataBase.class.php");
require_once(dirname(__FILE__) . "/managerDevices.class.php");
require_once(dirname(__FILE__) . '/globalConst.interface.php');

interface iUnit
{
    public function getId();
    public function getValue();
}

abstract class unit implements iUnit
{

    protected $device = null;
    protected $id = 0;
    protected $type = typeUnit::NONE;

    /**
     * unit constructor.
     * @param $type
     * @param $id
     * @param array $device
     * @throws Exception
     */
    public function __construct($type, $id, array $device)
    {
        $this->type = $type;
        $this->id = $id;
        $this->device = managerDevices::createDevice($device);
    }

    public function __destruct()
    {
        unset($this->device);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Получить/считать значение датчика
     * @return null
     */
    abstract public function getValue();

}

class sensorUnit extends unit
{

    protected $valueTable = 0;

    /**
     * @return int|mixed
     */
    public function getValueTable()
    {
        return $this->valueTable;
    }

    /**
     * sensorUnit constructor.
     * @param array $options
     * @param $type
     * @throws Exception
     */
    public function __construct(array $options, $type)
    {
        $sel = new selectOption();
        $sel->set('DeviceID', $options['DeviceID']);
        $arr = DB::getListDevices($sel);
        parent::__construct($type, $options['UnitID'], $arr[0]);
        $this->valueTable = $options['ValueTableID'];
    }

    /**
     * Считать данные непосредственно с физического датчика
     * @return mixed
     */
    public function getValue()
    {
        if (!is_null($this->device)) {
            return $this->device->getValue();
        }
        else {
            return null;
        }
    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }
}

class moduleUnit extends unit
{

    /**
     * moduleUnit constructor.
     * @param array $options
     * @param $type
     * @throws Exception
     */
    public function __construct(array $options, $type)
    {
        $sel = new selectOption();
        $sel->set('DeviceID', $options['DeviceID']);
        $arr = DB::getListDevices($sel);
        parent::__construct($type, $options['UnitID'], $arr[0]);
    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }

    public function getValue() {}

}

/**
 * Датчик температуры
 * Class temperatureUnit
 */
class temperatureUnit extends sensorUnit
{

    private $delta = 0;

    /**
     * temperatureUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::TEMPERATURE);
        $this->delta = $options['Delta'];
    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }

    /**
     * Записать значение температуры в базу данных
     * время записи берется текущее серверное
     * @param $value
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public function writeValue($value)
    {

        if (!is_double($value) && !is_int($value)) {
            //Пишем лог
            return;
        }

        $delta = $this->delta;
        $temperature = $value + $delta;
        $uniteID = $this->id;
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID, SYSDATE(), " . $temperature . ')';

        $con = sqlDataBase::Connect();

        $result = queryDataBase::execute($con, $query);

        unset($con);

        if (!$result) {
            logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

    }

    /**
     * Получить последнюю температуру из базы данных
     *
     */
    public function readValue()
    {
        $value = DB::getLastValueUnit($this);
        return $value;
    }

    /**
     * @param null $dateFrom
     * @param null $dateTo
     * @return array|null
     */
    public function getTemperatureForInterval($dateFrom = null, $dateTo = null)
    {

        //Конечная дата
        $date_to = "'" . $dateTo . "'";
        //Если конечная дата не задана, используем настоящее время
        if (empty($dateTo)) {
            $date_to = "NOW()";
        }

        //Обрабатываем начальную дату
        $date_from = $dateFrom;
        $date_format = "DATE_FORMAT(Date, '%d.%m')";

        if (empty($dateFrom) || $dateFrom == 'day') {
            $date_from = "($date_to - INTERVAL 1 DAY)";
            $date_format = "DATE_FORMAT(Date, '%H:%i')";
        }
        elseif ($dateFrom == "week") {
            $date_from = "($date_to - INTERVAL 7 DAY)";
            $date_format = "DATE_FORMAT(Date, '%d.%m')";
        }
        elseif ($dateFrom == "month") {
            $date_from = "($date_to - INTERVAL 1 MONTH)";
            $date_format = "DATE_FORMAT(Date, '%d.%m')";
        }

        $id = $this->getId();
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $query = "SELECT Value, $date_format Date_f FROM " . $nameTabValue . " WHERE UnitID=" . $id . " AND Date>=$date_from AND Date<=$date_to ORDER BY Date";

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::getAll($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции getTemperatureForIntervall. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции getTemperatureForInterval. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        }

        unset($con);

        return $result;

    }

}

class humidityUnit extends sensorUnit
{

    private $delta = 0;

    /**
     * temperatureUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::HUMIDITY);
        $this->delta = $options['Delta'];
    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }

    /**
     * Записать значение температуры в базу данных
     * время записи берется текущее серверное
     * @param $value
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public function writeValue($value)
    {

        if (!is_double($value) && !is_int($value)) {
            //Пишем лог
            return;
        }

        $delta = $this->delta;
        $temperature = $value + $delta;
        $uniteID = $this->id;
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID, SYSDATE(), " . $temperature . ')';

        $con = sqlDataBase::Connect();

        $result = queryDataBase::execute($con, $query);

        unset($con);

        if (!$result) {
            logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

    }

    /**
     * Получить последнюю температуру из базы данных
     *
     */
    public function readValue()
    {
        $value = DB::getLastValueUnit($this);
        return $value;
    }

}

class pressureUnit extends sensorUnit
{

    private $delta = 0;

    /**
     * pressureUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::PRESSURE);
        $this->delta = $options['Delta'];
    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }

    /**
     * Записать значение давления в базу данных
     * время записи берется текущее серверное
     * @param $value
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public function writeValue($value)
    {

        if (!is_double($value) && !is_int($value)) {
            //Пишем лог
            return;
        }

        $delta = $this->delta;
        $pressure = $value + $delta;
        $uniteID = $this->id;
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID, SYSDATE(), " . $pressure . ')';

        $con = sqlDataBase::Connect();

        $result = queryDataBase::execute($con, $query);

        unset($con);

        if (!$result) {
            logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

    }

    /**
     * Получить последнее давление из базы данных
     *
     */
    public function readValue()
    {
        $value = DB::getLastValueUnit($this);
        return $value;
    }

    /**
     * @param null $dateFrom
     * @param null $dateTo
     * @return array|null
     */
    public function getPressureForInterval($dateFrom = null, $dateTo = null)
    {

        //Конечная дата
        $date_to = "'" . $dateTo . "'";
        //Если конечная дата не задана, используем настоящее время
        if (empty($dateTo)) {
            $date_to = "NOW()";
        }

        //Обрабатываем начальную дату
        $date_from = $dateFrom;
        $date_format = "DATE_FORMAT(Date, '%d.%m')";

        if (empty($dateFrom) || $dateFrom == 'day') {
            $date_from = "($date_to - INTERVAL 1 DAY)";
            $date_format = "DATE_FORMAT(Date, '%H:%i')";
        }
        elseif ($dateFrom == "week") {
            $date_from = "($date_to - INTERVAL 7 DAY)";
            $date_format = "DATE_FORMAT(Date, '%d.%m')";
        }
        elseif ($dateFrom == "month") {
            $date_from = "($date_to - INTERVAL 1 MONTH)";
            $date_format = "DATE_FORMAT(Date, '%d.%m')";
        }

        $id = $this->getId();
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $query = "SELECT Value, $date_format Date_f FROM " . $nameTabValue . " WHERE UnitID=" . $id . " AND Date>=$date_from AND Date<=$date_to ORDER BY Date";

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::getAll($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции getPressureForInterval. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции getPressureForInterval. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        }

        unset($con);

        return $result;

    }

    /**
     * Получить среднее значение (давление) за интервал
     * @param null $dateFrom - начальная дата, задается количеством часов от начальной даты,
     * если пустая берется 1 час (среднее давление за час)
     * @param null $dateTo - конечная дата, задается в виде даты, если не задана или пустая,
     * то берется текущая дата
     * @return float
     */
    public function getAverageForInterval($dateFrom = null, $dateTo = null){
        //Конечная дата
        if (empty($dateTo)) {
            $dateTo = "NOW()";
        }
        else {
            $dateTo = '"'.$dateTo.'"';
        }

        //Начальная дата
        if (is_int($dateFrom)) {
            $dateFrom = '('.$dateTo.' - INTERVAL '.$dateFrom.' HOUR)';
        }
        else {
            $dateFrom = '('.$dateTo.' - INTERVAL 1 HOUR)';
        }

        $id = $this->getId();
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $query = 'SELECT avg(Value) AS Value FROM '.$nameTabValue.' WHERE UnitID='.$id.' AND Date>='.$dateFrom.' AND Date<='.$dateTo;

        $result = null;

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::getOne($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции getTemperatureForIntervall. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции getTemperatureForInterval. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
        }

        unset($con);

        $value = (double)$result['Value'];

        return $value;

    }

}

class keyInUnit extends sensorUnit
{

    private $lastValue;
    private $chanel = '';

    /**
     * keyInUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        $this->chanel = $options['Chanel'];
        parent::__construct($options, typeUnit::KEY_IN);
        $this->lastValue = null;
    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }

    /**
     * Считать данные непосредственно с физического датчика
     * @return mixed
     */
    public function getValue()
    {
        if (!is_null($this->device)) {
            return $this->device->getValue($this->chanel);
        }
        else {
            return null;
        }
    }

    public function readWhereLastStatus($status) //посмотреть в журнале когда было последннее значение равное value
    {
        if (is_null($status)) {
            return null;
        }

        $lastValue = DB::getLastValueUnit($this, $status);

        if (is_null($lastValue)) {
            return null;
        }
        else {
            return $lastValue['Date'];
        }
    }

    public function checkLastStatus() //посмотреть в журнале последннее значение value
    {
        $lastValue = DB::getLastValueUnit($this);

        if (is_null($lastValue)) {
            return null;
        }
        else {
            return $lastValue['Value'];
        }
    }

    public function updateStatus($status) //проверить статус если он изменился с последнего времени, то добавить новый
    {
        if (is_null($status)) return;

        if (is_null($this->lastValue)) {  // если последнне значение не известно
            $lastStatus = $this->checkLastStatus(); // то читаем его из базы
            if (is_null($lastStatus)) { //если в базе его тоже нет, то условимся на 0
                $lastStatus = 0;
                $this->writeValue($lastStatus); // и запишем его в базе
            }
            $this->lastValue = $lastStatus;
        }
        else {
            $lastStatus = $this->lastValue; // если последнее значение есть в буфере берем его
        }

        if ($status != $lastStatus) {
            $this->writeValue($status);
            $this->lastValue = $status;
        }
    }

    /**
     * @param $value
     */
    private function writeValue($value) //записать в журнал значение
    {
        if (!is_int($value)) {
            //Пишем лог
            return;
        }

        $uniteID = $this->getId();
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID, SYSDATE(), " . (int)$value . ')';

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::execute($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции writeValue. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции writeValue. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        }
        unset($con);

        if (!$result) {
            logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }
    }

}

class powerKeyUnit extends moduleUnit
{

    private $chanel = '';

    /**
     * powerKeyUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        $this->chanel = $options['Chanel'];
        parent::__construct($options, typeUnit::POWER_KEY);
    }

    public function setValue($value, $status = null)
    {
        if (is_null($value)) {
            return;  //Пишем лог
        }

        if (is_null($this->device)) {
            return;  //Пишем лог
        }

        $result = $this->device->setValue($value, $this->chanel);
        if ($result) {
            $this->writeStatusKeyJournal($status); //записываем в журнал каким образом изменилось значение
        }

    }

    /**
     * Считать данные непосредственно с физического датчика
     * @return mixed
     */
    public function getValue()
    {
        if (!is_null($this->device)) {
            return $this->device->getValue($this->chanel);
        }
        else {
            return null;
        }
    }

    /**
     * @param $status
     */
    private function writeStatusKeyJournal($status)
    {
        if (!is_string($status)) {
            return;
        }

        $uniteID = $this->getId();

        $query = 'INSERT INTO tjournalkey VALUES (NULL, ' . "$uniteID" . ', SYSDATE(), "' . $status . '")';

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::execute($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции writeStatusKeyJournal. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции writeStatusKeyJournal. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            $result = null;
        }
        unset($con);

        if (!$result) {
            logger::writeLog('Ошибка при записи в базу данных (writeStatusKeyJournal)',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }
    }

    /**
     * посмотреть в журнале последнюю запись
     * @return null
     */
    public function readLastRecordKeyJournal()
    {
        $lastRecord = DB::getLastStatusKeyJournal($this);

        if (is_null($lastRecord)) {
            return null;
        }
        else {
            return $lastRecord;
        }
    }


}