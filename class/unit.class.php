<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:13
 */

require_once(dirname(__FILE__) . '/sqlDataBase.class.php');
require_once(dirname(__FILE__) . '/managerDevices.class.php');
require_once(dirname(__FILE__) . '/globalConst.interface.php');
require_once(dirname(__FILE__) . '/logger.class.php');

interface iUnit
{
    /**
     * Получить универсальный ID модуля
     * @return int
     */
    public function getId();

    /**
     * Получить имя модуля
     * @return string
     */
    public function getLabel();

    /**
     * Получить физическое устройство модуля
     * @return iDevice|null
     */
    public function getDevice();

    /**
     * Проверяет, есть ли у устройства подписка статуса MQTT. Если подписка есть, возвращает подписку иначе null.
     * @return string|null
     */
    public function checkMQTTTopicStatus();

    /**
     * Проверяет, есть ли у устройства топик для публикации MQTT. Если топик есть, возвращает топик иначе null.
     * @return string|null
     */
    public function checkMqttTopicPublish();

    /**
     * Получить данные с модуля
     * (извлекаются готовые данные из модуля, не с физического датчика)
     * @return mixed
     */
    public function getValues();

    public function test();
}

interface iSensorUnite extends iUnit {

    /**
     * Возвращает тип устройства привязанного к модулю, значение датчика можно получить в любое время или
     * надо датчик постоянно "слушать"
     * @return mixed
     */
    public function getModeDeviceValue();

    /**
     * Обновляем значение модуля
     * @param $value
     */
    public function updateValue($value);

    public function getValues();

    /**
     * Получить значение непосредственно с физического сенсора
     * @return mixed
     */
    public function getValueFromDevice();

}

interface iModuleUnite extends iUnit {


}

abstract class unit implements iUnit
{
    private $smKey; //Числовой идентификатор сегмента разделяемой памяти
    protected $device = null;
    protected $id = 0;
    protected $label = '';
    protected $type = typeUnit::NONE;
    protected $value = null;

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

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Получить физическое устройство модуля
     * @return iDevice|null
     */
    public function getDevice() {
        if (!is_object($this->device)) {
            return null;
        }
        return $this->device;
    }

    public function getValues() {
        return json_encode([
            'value' => $this->value,
        ]);
    }

    /** Добавляет модуль в распределяемую память, возвращает id модуля, при ошибке добавления возвращает null
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
    public function getDeviceAddress() {
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
     * Получить у устройства топик подписки статуса MQTT
     * @return string|null
     */
    private function getMQTTTopicStatus() {
        if (is_null($this->device)) {
            return null;
        }
        return $this->device->getTopicStat();
    }

    /**
     * Проверяет, есть ли у устройства подписка статуса MQTT. Если подписка есть, возвращает подписку иначе null.
     * @return string|null
     */
    public function checkMQTTTopicStatus() {
        if (is_null($this->device)) {
            return null;
        }
        if ($this->device->getNet()!=netDevice::ETHERNET_MQTT) {
            return null;
        }
        $topicStat = $this->getMQTTTopicStatus();
        if (is_null($topicStat) || trim($topicStat) === '') {
            return null;
        }
        return $topicStat;
    }

    /**
     * Получить у устройства топик публикации MQTT
     * @return string|null
     */
    private function getMQTTTopicCommand() {
        if (is_null($this->device)) {
            return null;
        }
        return $this->device->getTopicCmnd();
    }

    /**
     * Проверяет, есть ли у устройства топик для публикации MQTT. Если топик есть, возвращает топик иначе null.
     * @return string|null
     */
    public function checkMqttTopicPublish() {
        if (is_null($this->device)) {
            return null;
        }
        if ($this->device->getNet()!=netDevice::ETHERNET_MQTT) {
            return null;
        }
        $topicStat = $this->getMQTTTopicCommand();
        if (is_null($topicStat) || trim($topicStat) === '') {
            return null;
        }
        return $topicStat;
    }

    /**
     * Обновляем значение модуля и время обновления значения
     * @param $value
     * @return void
     */
    public function updateValue($value) {
        $this->value = $value;
    }

    /**
     * Конвертирует входящее значение полученное от MQTT брокера в значение датчика
     * @param $payload
     * @return mixed
     */
    protected function convertPayload($payload) {
        return $payload;
    }

    /**
     * Обновляем значение модуля и время обновления значения пришедшее по подписке MQTT
     * @param $payload
     */
    abstract public function updateValueMQTT($payload);

    /**
     * @return mixed
     */
    public function getSmKey()
    {
        return $this->smKey;
    }

    /**
     *  Обновляет модуль в распределяемой памяти
     */
    public function updateUnitSharedMemory() {
        sharedMemoryUnit::set($this);
    }

    public function test() {
        $device = $this->getDevice();
        if (is_null($device)) {
            return testUnitCode::NO_DEVICE;
        }
        return $device->test();
    }

    public function getValuesForInterval($dateFrom = null, $dateTo = null) {
        return null;
    }

    public function getValueAverageForInterval() {
        return null;
    }
}

abstract class sensorUnit extends unit implements iSensorUnite
{

    protected $valueTable = 0;
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
     * Получить/считать значение с физического датчика
     * @return string|null
     */
    public function getValueFromDevice() {
        if (!is_null($this->device)) {
            return $this->device->getValue();
        }
        else {
            return null;
        }
    }

    /**
     * Возвращает тип устройства (device) привязанного к модулю, значение датчика можно получить в любое время или
     * надо датчик постоянно "слушать", или неопределённо = null
     * @return int|mixed|null
     */
    public function getModeDeviceValue()
    {
        $result = modeDeviceValue::IS_NULL;
        if (is_null($this->device)) {
            return $result;
        }
        if ($this->device->getNet() == netDevice::ONE_WIRE) {
            $result = modeDeviceValue::GET_VALUE;
        }
        return $result;
    }

    public function updateValue($value) {
        parent::updateValue($value);
        $this->dataValue = time();
    }

    /** Получить последнее записанное значение датчика из базы данных
     * @return array|null
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
    public function getValuesForInterval($dateFrom = null, $dateTo = null, $dataFormat = null)
    {

        $result = parent::getValuesForInterval();

        //Конечная дата
        $dateToQuery = "'" . $dateTo . "'";
        //Если конечная дата не задана, используем настоящее время
        if (empty($dateTo)) {
            $dateToQuery = 'NOW()';
        }

        //Обрабатываем начальную дату
        $dateFromQuery = $dateFrom;
        if (!is_null($dataFormat)) {
            //$date_format = "DATE_FORMAT(Date, '%H:%i')";
            $dataFormatQuery = 'DATE_FORMAT(Date, \''.$dataFormat.'\')';
        }
        elseif ($dateFrom == 'week' || $dateFrom == 'month') {
            $dataFormatQuery = "DATE_FORMAT(Date, '%d.%m')";
        }
        else {
            $dataFormatQuery = "DATE_FORMAT(Date, '%H')";
        }

        if (empty($dateFrom) || $dateFrom == 'day') {
            $dateFromQuery = "($dateToQuery - INTERVAL 1 DAY)";
        }
        elseif ($dateFrom == 'week') {
            $dateFromQuery = "($dateToQuery - INTERVAL 7 DAY)";
        }
        elseif ($dateFrom == 'month') {
            $dateFromQuery = "($dateToQuery - INTERVAL 1 MONTH)";
        }

        $id = $this->getId();
        $nameTabValue = 'tvalue_' . $this->valueTable;

//        $query = 'SELECT Value, '.$date_format.' Date_f FROM '.$nameTabValue.' WHERE UnitID='.$id.' AND Date>='.$dateFromQuery.' AND Date<='.$dateToQuery.' ORDER BY Date';
        /** @noinspection SqlResolve */
        $query = 'SELECT Value,'.$dataFormatQuery.' Date_f FROM '.$nameTabValue.' WHERE UnitID='.$id.' AND Date>='.$dateFromQuery.' AND Date<='.$dateToQuery.' ORDER BY Date';

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::getAll($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции getTemperatureForInterval. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции getTemperatureForInterval. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
        }

        unset($con);
        return $result;

    }

    /**
     * Получить среднее значение из базы за интервал
     * @param null $date - конечная дата, задается в виде даты, если не задана или пустая,
     * то берется текущая дата
     * @param null $intervalHour - количество часов от начальной даты,
     * если пустая берется -1 час (среднее значение за час до начальной даты)
     * @return float
     */
    public function getValueAverageForInterval($date = null, $intervalHour = null) {

        $result = parent::getValueAverageForInterval();

        //исходная дата
        if (empty($date)) {
            $date1 = 'NOW()';
        } else {
            $date1 = '\''.$date.'\'';
        }

        //интервал
        if (!is_int($intervalHour) || $intervalHour == 0) {
            $intervalHour = -1;
        }
        $date2 = '('.$date1.' '.($intervalHour>0?'+ ':'- ').'INTERVAL '.abs($intervalHour).' HOUR)';

        if ($intervalHour>0) {
            $dateFrom = $date1;
            $dateTo = $date2;
        } else {
            $dateFrom = $date2;
            $dateTo = $date1;
        }

        $id = $this->getId();
        $nameTabValue = 'tvalue_' . $this->valueTable;

        /** @noinspection SqlResolve */
        $query = 'SELECT avg(Value) AS Value FROM '.$nameTabValue.' WHERE UnitID='.$id.' AND Date>='.$dateFrom.' AND Date<='.$dateTo;

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::getOne($con, $query);
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции getValueAverageForInterval. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка в функции getValueAverageForInterval. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
        }
        unset($con);

        return $result['Value'];
    }
}

abstract class moduleUnit extends unit implements iModuleUnite
{

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

    public function readValue()
    {
        return null;
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

    /**
     * Получить значение температуры непосредственно с датчика
     * @return null
     */
    public function getValueFromDevice() {
        if ($this->getModeDeviceValue() != modeDeviceValue::GET_VALUE) {
            return null;
        }
        return parent::getValueFromDevice();
    }

    /**
     * Обновляет значение модуля и время получения этого значения, помещает объект модуля в распределяемую память
     * @param $value
     */
    public function updateValue($value) {
        if (is_null($value)) return;
        parent::updateValue($value);
        $this->updateUnitSharedMemory();
    }

    /**
     * Обновляет значение модуля и время получения этого значения, помещает объект модуля в распределяемую память
     * и записывает в базу данных
     * @param $payload
     */
    public function updateValueMQTT($payload) {
        $value = $this->convertPayload($payload);
        $this->updateValue($value);
        try {
            $this->writeCurrentValueDB();
        } catch (connectDBException $e) {
            logger::writeLog('ошибка подключения к базе данных', loggerTypeMessage::ERROR,loggerName::MQTT);
        } catch (querySelectDBException $e) {
            logger::writeLog('ошибка добавление температуры в базу данных', loggerTypeMessage::ERROR,loggerName::MQTT);
        }
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

        $temperature = $value + (float)$this->delta;
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
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public function writeCurrentValueDB()
    {

        $temperature = $this->value + (float)$this->delta;
        $uniteID = $this->id;
        $nameTabValue = 'tvalue_' . $this->valueTable;
        $dateValue = date('Y-m-d H:i:s',$this->dataValue);

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID,"." '$dateValue',"  . $temperature . ')';

        $con = sqlDataBase::Connect();

        $result = queryDataBase::execute($con, $query);

        unset($con);

        if (!$result) {
            logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

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

    /**
     * Записать значение давления в базу данных
     * время записи берется текущее серверное
     * @param $value
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public function writeCurrentValueDB()
    {

        $pressure = $this->value + (float)$this->delta;
        $uniteID = $this->id;
        $nameTabValue = 'tvalue_' . $this->valueTable;
        $dateValue = date('Y-m-d H:i:s',$this->dataValue);

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID,"." '$dateValue',"  . $pressure . ')';

        $con = sqlDataBase::Connect();

        $result = queryDataBase::execute($con, $query);

        unset($con);

        if (!$result) {
            logger::writeLog('Ошибка при записи в базу данных (writeCurrentValueDB)',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

    }

    public function updateValueMQTT($payload) {
        $value = $this->convertPayload($payload);
        $this->updateValue($value);
        try {
            $this->writeCurrentValueDB();
        } catch (connectDBException $e) {
            logger::writeLog('ошибка подключения к базе данных', loggerTypeMessage::ERROR,loggerName::MQTT);
        } catch (querySelectDBException $e) {
            logger::writeLog('ошибка добавление температуры в базу данных', loggerTypeMessage::ERROR,loggerName::MQTT);
        }
    }

    /**
     * Обновляет значение модуля и время получения этого значения, помещает объект модуля в распределяемую память
     * @param $value
     */
    public function updateValue($value) {
        if (is_null($value)) return;
        parent::updateValue($value);
        $this->updateUnitSharedMemory();
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

    /**
     * Записать значение давления в базу данных
     * время записи берется текущее серверное
     * @param $value
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public function writeCurrentValueDB()
    {

        $pressure = $this->value + (float)$this->delta;
        $uniteID = $this->id;
        $nameTabValue = 'tvalue_' . $this->valueTable;
        $dateValue = date('Y-m-d H:i:s',$this->dataValue);

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID,"." '$dateValue',"  . $pressure . ')';

        $con = sqlDataBase::Connect();

        $result = queryDataBase::execute($con, $query);

        unset($con);

        if (!$result) {
            logger::writeLog('Ошибка при записи в базу данных (writeCurrentValueDB)',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

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
            $date_to = 'NOW()';
        }

        //Обрабатываем начальную дату
        $date_from = $dateFrom;
        $date_format = "DATE_FORMAT(Date, '%d.%m')";

        if (empty($dateFrom) || $dateFrom == 'day') {
            $date_from = "($date_to - INTERVAL 1 DAY)";
            $date_format = "DATE_FORMAT(Date, '%H:%i')";
        }
        elseif ($dateFrom == 'week') {
            $date_from = "($date_to - INTERVAL 7 DAY)";
            $date_format = "DATE_FORMAT(Date, '%d.%m')";
        }
        elseif ($dateFrom == 'month') {
            $date_from = "($date_to - INTERVAL 1 MONTH)";
            $date_format = "DATE_FORMAT(Date, '%d.%m')";
        }

        $id = $this->getId();
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $query = "SELECT Value, $date_format Date_f FROM " . $nameTabValue . ' WHERE UnitID=' . $id . " AND Date>=$date_from AND Date<=$date_to ORDER BY Date";

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

    public function updateValueMQTT($payload) {
        $value = $this->convertPayload($payload);
        $this->updateValue($value);
        try {
            $this->writeCurrentValueDB();
        } catch (connectDBException $e) {
            logger::writeLog('ошибка подключения к базе данных', loggerTypeMessage::ERROR,loggerName::MQTT);
        } catch (querySelectDBException $e) {
            logger::writeLog('ошибка добавление температуры в базу данных', loggerTypeMessage::ERROR,loggerName::MQTT);
        }
    }

    /**
     * Обновляет значение модуля и время получения этого значения, помещает объект модуля в распределяемую память
     * @param $value
     */
    public function updateValue($value) {
        if (is_null($value)) return;
        parent::updateValue($value);
        $this->updateUnitSharedMemory();
    }

}

class keyInUnit extends sensorUnit
{

    private $lastValue;
    private $lastDataValue;

    /**
     * keyInUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::KEY_IN);
        $this->value = 0;
        $this->dataValue = time();
        $this->lastValue = 0;
        $this->lastDataValue = time();

    }

    public function readWhereLastStatus($status) //посмотреть в журнале когда было последнее значение равное value
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

    public function checkLastStatus() //посмотреть в журнале последнее значение value
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

        if (is_null($this->lastValue)) {  // если последнее значение не известно
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
     * Получить из объекта датчика его значение (состояние), статус и дату изменения статуса и последнее отличное
     * от текущего состояние и дату изменения этого состояния
     * @return false|string
     */
    public function getValues()
    {
        $values = json_decode(parent::getValues(), true);
        $value = $values['value'];
        return json_encode([
            'value' => $value,
            'dataValue' => $this->dataValue,
            'lastValue' => $this->lastValue,
            'lastDataValue' => $this->lastDataValue,
            ]);
    }

    /**
     * Обновляет в объекте датчика его значение и время получения этого значения
     * @param $value - значение датчика
     * @return void
     */
    public function updateValue($value) {
        if ($this->value === $value) {
            return;
        }
        $this->lastValue = $this->value;
        $this->lastDataValue = $this->dataValue;
        parent::updateValue($value);
        $this->updateUnitSharedMemory();
    }

    /**
     * Обновляет значение модуля и время получения этого значения, помещает объект модуля в распределяемую память
     * и записывает в базу данных
     * @param $payload
     */
    public function updateValueMQTT($payload) {
        $value = $this->convertPayload($payload);
        $this->updateValue($value);
    }

    public function updateDeviceAlarm() {
        if (!is_null($this->device)) {
            $this->device->updateAlarm();
        }
    }

    protected function convertPayload($payload) {
        if (is_string($payload)) {
            if (strtoupper($payload) == 'OFF' || strtoupper($payload) == 'FALSE' || $payload == '0') {return 0;}
            if (strtoupper($payload) == 'ON' || strtoupper($payload) == 'TRUE' || $payload == '1') {return 1;}
        }
        if (is_int($payload)) {
            if ($payload == 0) {return 0;}
            else {return 1;}
        }
        if (is_bool($payload)) {
            if ($payload) {return 1;}
            else {return 0;}
        }
        return 0;
    }

}

//Силовой ключ
class powerKeyUnit extends moduleUnit
{

    private $channel;

    /**
     * powerKeyUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        $this->channel = $options['Chanel'];
        parent::__construct($options, typeUnit::POWER_KEY);
    }

    /**
     * Отправляем на физический датчик значение, обновляем все свойства объекта модуля
     * и обновляем модуль в разделяемой памяти
     * @param $value
     * @param null $status - каким образом поменялось состояние (вручную, от датчика и т.д.)
     */
    public function updateValue($value, $status = null, $timePause = '')
    {
        if (is_null($value)) {
            return;  //Пишем лог
        }

        if (is_null($this->device)) {
            return;  //Пишем лог
        }

        if ($value!=$this->value) {
            $result = $this->device->setValue($value, $this->channel, $status, $timePause);
            //если связь не через MQTT, то обновляем значение, статус, и время сразу
            //если через MQTT, то состояние и статус придут от модуля
            if (is_null($this->checkMQTTTopicStatus())) {
                if ($result) {
                    $this->value = $value;
                    $this->status = $status;
                    $this->dataStatus = time();
                    $this->updateUnitSharedMemory();
                }
            }
        }
    }

    /**
     * Считать состояние модуля
     * @param bool $fromSensor - считать непосредственно с датчика или взять состояние из объекта модуля
     * @return mixed
     */
    public function getValueFromDevice($fromSensor = false)
    {
        if ($fromSensor) {
            if (!is_null($this->device)) {
                return $this->device->getValue($this->channel);
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

    public function updateValueMQTT($payload, $status = null)
    {
        if (is_null($this->device)) {
            logger::writeLog('У модуля ID '.$this->getId().' нет device', loggerTypeMessage::WARNING, loggerName::ERROR);
            return;
        }

        $value = json_decode($this->convertPayload($payload), true);
        if (is_null($value['code'])) {
            logger::writeLog('С модуля ID '.$this->getId().' не пришло значение', loggerTypeMessage::WARNING, loggerName::ERROR);
            return;  //Пишем лог
        }

        if ($value['code']!=$this->value || $value['status']!=$this->status) {
            if (!is_null($this->checkMQTTTopicStatus())) {
                $this->value = $value['code'];
                $this->status = $value['status'];
                $this->dataStatus = time();
                $this->updateUnitSharedMemory();
            }
        }
    }

    protected function convertPayload($payload)
    {
        $code = null;
        $status = null;
        if (is_string($payload)) { //может прийти команда и статус
            $p = explode(MQTT_CODE_SEPARATOR, $payload);
            if (strtoupper($p[0]) == 'OFF' || strtoupper($p[0]) == 'FALSE' || $p[0] == '0') {$code = 0;}
            if (strtoupper($p[0]) == 'ON' || strtoupper($p[0]) == 'TRUE' || $p[0] == '1') {$code = 1;}
            if (count($p) > 1) {
                $status = $p[1];
            }
            else {
                $status = statusKey::UNKNOWN;
            }
        }
        if (is_int($payload)) {
            if ($payload == 0) {$code = 0;}
            else {$code = 1;}
        }
        if (is_bool($payload)) {
            if ($payload) {$code = 1;}
            else {$code = 0;}
        }
        return json_encode([
        'code' => $code,
        'status' => $status,
        ]);

    }
}