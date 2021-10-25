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
    public function getLabel();
    public function getValue();
}

interface iSensorUnite extends iUnit {

    /**
     * Возвращает тип устройства привязанного к модулю, значение датчика можно получить в любое время или
     * надо датчик постоянно "слушать"
     * @return mixed
     */
    public function getModeDeviceValue();

}

abstract class unit implements iUnit
{
    private $smKey; //Числовой идентификатор сегмента разделяемой памяти
    protected $device = null;
    protected $id = 0;
    protected $label = '';
    protected $type = typeUnit::NONE;

    /**
     * unit constructor.
     * @param $type
     * @param $id
     * @param $label
     * @param $deviceID
     * @throws Exception
     */
    public function __construct($type, $id, $label, $deviceID)
    {
        $this->type = $type;
        $this->id = intval($id);
        $this->label = $label;

        $device = DB::getDeviceID($deviceID);
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

    /** Добавляет модуль в распределяемую память, возращает id модуля, при ошибке добавления возвращает null
     * @param $key - числовой идентификатор сегмента разделяемой памяти
     * @return int|null
     */
    public function initUnit($key)
    {
        $this->smKey = $key;
        return sharedMemoryUnit::set($this);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function checkDeviceDisabled () {
        if (is_null($this->device)) {
            return null;
        }
        return $this->device->getDisabled();
    }

    /**
     * Получить адрес устройства
     * @return int|null
     */
    public function getDeviceAdress() {
        if (is_null($this->device)) {
            return null;
        }
        return $this->device->getAddress();
    }

    /**
     * Проверяет, относится ли устройство к сети 1-wire, и установлен ли у нет условный поиск
     * @return bool
     */
    public function check1WireLoop() {
        if (is_null($this->device)) {
            return false;
        }
        if ($this->device->getNet()!=netDevice::ONE_WIRE) {
            return false;
        }
        if (is_null($this->device->getAlarm())) {
            return false;
        }
        return true;
    }

    /**
     * Получить у устройства подписку статуса MQQT
     * @return string|null
     */
    protected function getMQQTTopicStatus() {
        if (is_null($this->device)) {
            return null;
        }
        return $this->device->getTopicStat();
    }

    /**
     * Проверяет, есть ли у устройства подписка статуса MQQT. Если подписка есть, возвращает подписку иначе null.
     * @return string|null
     */
    public function checkMQQTTopicStatus() {
        if (is_null($this->device)) {
            return null;
        }
        if ($this->device->getNet()!=netDevice::ETHERNET_MQTT) {
            return null;
        }
        $topicStat = $this->getMQQTTopicStatus();
        if (is_null($topicStat) || $topicStat === "") {
            return null;
        }
        return $topicStat;
    }

    /**
     * @return mixed
     */
    public function getSmKey()
    {
        return $this->smKey;
    }

    /**
     *  Обновляет модуль в рапределяемой памяти
     */
    public function updateUnitSharedMemory() {
        sharedMemoryUnit::set($this);
    }

}

class sensorUnit extends unit implements iSensorUnite
{

    protected $valueTable = 0;
    protected $value = null;
    protected $dataValue = null;

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
//        $sel = new selectOption();
//        $sel->set('DeviceID', $options['DeviceID']);
//        $arr = DB::getListDevices($sel);
//        parent::__construct($type, $options['UnitID'], $options['UnitLabel'], $arr[0]);
        $this->valueTable = $options['ValueTableID'];
        parent::__construct($type, $options['UnitID'], $options['UnitLabel'], $options['DeviceID']);

    }

    /**
     * Получить/считать значение датчика
     * @return null
     */
    public function getValue() {
        if (!is_null($this->device)) {
            return $this->device->getValue();
        }
        else {
            return null;
        }
    }

    /**
     * Считывает c физического датчика (device) значение и обновялет его и обновляет время получения этого значения
     * @return null|string
     */
    public function updateValue() {
        $this->value = $this->getValue();
        $this->dataValue = time();
        if (is_null($this->value)) {
            return null;
        }
        return json_encode([
            'value' => $this->value,
            'dateValue' => $this->dataValue,
        ]);
    }

    /**
     * Возвращает тип устройства (device) привязанного к модулу, значение датчика можно получить в любое время или
     * надо датчик постоянно "слушать", или неопределен = null
     * @return int|mixed|null
     */
    public function getModeDeviceValue()
    {
        return modeDeviceValue::IS_NULL;
    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }
}

class moduleUnit extends unit
{

    protected $value = null; //значение
    protected $status = null; //статус, как изменил свое состояние
    protected $dataStatus = null; //время изменения состояния

    /**
     * moduleUnit constructor.
     * @param array $options
     * @param $type
     * @throws Exception
     */
    public function __construct(array $options, $type)
    {
//        $sel = new selectOption();
//        $sel->set('DeviceID', $options['DeviceID']);
//        $arr = DB::getListDevices($sel);
//        parent::__construct($type, $options['UnitID'], $options['UnitLabel'], $arr[0]);
        parent::__construct($type, $options['UnitID'], $options['UnitLabel'], $options['DeviceID']);

    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
    }

    public function getValue() {
        return null;
    }

    public function setMode($mode) {
    }

    /**
     * Получить режим работы модуля из БД
     * @return mixed
     */
    public function getMode()
    {
        return DB::getModeUnit($this);
    }

    public function updateValue($value, $status = null)
    {
    }
}

/**
 * Датчик температуры
 * Class temperatureUnit
 */
class temperatureUnit extends sensorUnit
{

    private $delta;

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
     * Считывает и обновялет значение датчика (device) и время получения этого значения,
     * помещает объект модуля в распределяемую память
     *
     * @return mixed|null
     */
    public function updateValue() {
        $result = json_decode(parent::updateValue(), true);
        $this->updateUnitSharedMemory();
        if (is_null($result)) {
            return null;
        }
        return json_encode([
            'value' => $result['value'],
            'dateValue' => $result['dateValue'],
        ]);
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
     * Записать значение температуры в базу данных
     * @param $value
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public function writeCurrentValueDB()
    {

//        $value = json_decode($result, true);
//
//        if (!is_double($value['value']) && !is_int($value['value'])) {
//            //Пишем лог
//            return;
//        }

        $temperature = $this->value + (int)$this->delta;
        $uniteID = $this->id;
        $nameTabValue = 'tvalue_' . $this->valueTable;
        $dateValue = date("Y-m-d H:i:s",$this->dataValue);

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID,"." '$dateValue',"  . $temperature . ')';

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
        return DB::getLastValueUnit($this);
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
            logger::writeLog('Ошибка при подключении к базе данных в функции getTemperatureForInterval. ' . $e->getMessage(),
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

    public function getModeDeviceValue() {
        $result = modeDeviceValue::IS_NULL;
        if (is_null($this->device)) {
            return $result;
        }
        if ($this->device->getNet() == netDevice::ONE_WIRE) {
            $result = modeDeviceValue::GET_VALUE;
        }
        return $result;
    }

}

class humidityUnit extends sensorUnit
{

    private $delta;

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
        return DB::getLastValueUnit($this);
    }

}

class pressureUnit extends sensorUnit
{

    private $delta;

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
        return DB::getLastValueUnit($this);
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

        return (double)$result['Value'];

    }

}

class keyInUnit extends sensorUnit
{

    private $lastValue;
    private $lastDataValue;
    private $chanel;

    /**
     * keyInUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        $this->chanel = $options['Chanel'];
        parent::__construct($options, typeUnit::KEY_IN);
        $this->value = 0;
        $this->dataValue = time();
        $this->lastValue = 0;
        $this->lastDataValue = time();

    }

    public function __destruct()
    {
        parent::__destruct(); // TODO: Change the autogenerated stub
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
     * Записать в журнал значение
     * @param $value
     */
    private function writeValue($value)
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

    /**
     * Значение датчика
     * @param bool $fromSensor - считать данные непосредственно с физического датчика или
     * иначе взять значение из свойста объекта
     * @return mixed
     */
    public function getValue($fromSensor = false)
    {
        if ($fromSensor) {
            if (!is_null($this->device)) {
                return $this->device->getValue($this->chanel);
            } else {
                return null;
            }
        }
        else {
            return $this->value;
        }
    }

    /**
     * Получить из объекта датчикка его значение (состояние), статус и дату изменения статуса и последнее отличное
     * от текущего состояние и дату изменения этого состояния
     * @return false|string
     */
    public function getValues()
    {
        return json_encode([
            'value' => $this->value,
            'dataValue' => $this->dataValue,
            'lastValue' => $this->lastValue,
            'lastDataValue' => $this->lastDataValue,
            ]);
    }

    /**
     * Обновляет в объекте датчика его значение и время получения этого значения
     * @param $value - значение датчика
     * @return null|string
     */
    public function updateValueLoop($value) {
        if ($this->value === $value) {
            return;
        }
        $this->lastValue = $this->value;
        $this->lastDataValue = $this->dataValue;
        $this->value = $value;
        $this->dataValue = time();
    }

    public function updateDeviceAlarm() {
        if (!is_null($this->device)) {
            $this->device->updateAlarm();
        }
    }

}

//Силовой ключ
class powerKeyUnit extends moduleUnit
{

    private $chanel;

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

    /**
     * Отправляем на физический датчик значение и
     * записываем в журнал когда и каким образом изменилось состояние ключа
     * @param $value
     * @param null $status - каким образом поменялось состояние (вручную, от датчика и т.д.)
     */
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
     * Отправляем на физический датчик значение, обновляем все свойства объекта модуля
     * и обновляем модуль в разделяемой памяти
     * @param $value
     * @param null $status - каким образом поменялось состояние (вручную, от датчика и т.д.)
     */
    public function updateValue($value, $status = null)
    {
        if (is_null($value)) {
            return;  //Пишем лог
        }

        if (is_null($this->device)) {
            return;  //Пишем лог
        }

        if ($value!=$this->value) {
            $result = $this->device->setValue($value, $this->chanel);
            if ($result) {
                $this->value = $value;
                $this->status = $status;
                $this->dataStatus = time();
                $this->updateUnitSharedMemory();
            }
        }

    }

    /**
     * Считать состояние модуля
     * @param bool $fromSensor - считать непоссредственно с датчика или вязть состояние из объекта модуля
     * @param null $chanel
     * @return mixed
     */
    public function getValue($fromSensor = false)
    {
        if ($fromSensor) {
            if (!is_null($this->device)) {
                return $this->device->getValue($this->chanel);
            } else {
                return null;
            }
        }
        else {
            return $this->value;
        }
    }

    /**
     * Получить из объекта модуля его значение (состояние), статус и дату изменения статуса
     * @return false|string
     */
    public function getValues() {
        return json_encode([
            'value' => $this->value,
            'status' => $this->status,
            'dataStatus' => $this->dataStatus
        ]);
    }

    /**
     * Записать в журнал когда и каким образом изменилось состояние ключа
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