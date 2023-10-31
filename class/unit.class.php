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

interface iUniteOptions
{
    function get($options);

    function set($option, $value);

    function setOptions($options);
}

abstract class aUniteOptions
{
    protected $unitID;

    public function __construct($unitID)
    {
        $this->unitID = $unitID;
    }

    abstract function get($options);

    abstract function set($option, $value);

    abstract function setOptions($options);
}

class uniteOptionsJSON extends aUniteOptions
{
    private $data = [];
    private $default;

    public function __construct($unitID, $jsonData, $default)
    {
        parent::__construct($unitID);
        if (is_string($jsonData)) $this->data = json_decode($jsonData, true);
        $this->default = $default;
    }

    function get($options)
    {
        if (array_key_exists($options, $this->data)) {
            $result = is_null($this->data[$options]) ? $this->default->{$options} : $this->data[$options];
        } else {
            $result = $this->default->{$options};
        }
        return $result;
    }

    function set($option, $value)
    {
        $this->data[$option] = $value;
        $this->save();
    }

    private function save()
    {
        $jsonData = json_encode($this->data);
        $query = sprintf('UPDATE tunits SET Options = \'%s\' WHERE UnitID = %s',
            $jsonData, $this->unitID);

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::execute($con, $query);
            if (!$result) {
                logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                    loggerTypeMessage::ERROR, loggerName::ERROR);
            }
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при добавлении данных в базу данных',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }
        unset($con);
    }

    function setOptions($options) {
        if (is_string($options)) {
            $jsonData = json_decode($options, true);
            foreach ($jsonData as $key=>$value)
            $this->data[$key] = $value;
        }
        $this->save();
    }

}

interface iUnit
{
    /**
     * Получить универсальный ID модуля
     * @return int
     */
    function getId();

    /**
     * Получить имя модуля
     * @return string
     */
    function getLabel();

    function getDevice();

    function getData();

    function getOptions();

}

interface iSensorUnite extends iUnit
{

    function getValueTable();

    function getDelta();

}

interface iModuleUnite extends iUnit
{

    function setData($data);

}

abstract class unit implements iUnit
{
    protected $id = 0;
    protected $label = '';
    protected $type = typeUnit::NONE;
    private $device;
    private $options;

    public function __construct($type, $id, $label, $deviceID, $options)
    {
        $this->id = intval($id);
        $this->type = $type;
        $this->label = $label;
        $this->options = $options;
        $this->device = managerDevices::getDevice($deviceID);

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
    public function getDevice()
    {
        if (is_a($this->device, 'aDevice')) {
            return $this->device;
        }
        return null;
    }

    public function getData()
    {
        $device = $this->getDevice();
        if (!is_null($device)) {
            $data = $device->getData();
        } else {
            $dataValue = new deviceDataValue();
            $dataValue->setDefaultValue();
            $data = $dataValue->getDataJSON();
        }
        return $data;
    }

    function getOptions()
    {
        return is_null($this->options) ? '{}' : $this->options;
    }
}

abstract class sensorUnit extends unit implements iSensorUnite
{

    protected $delta = 0.0;
    protected $valueTable = 0;

    /**
     * sensorUnit constructor.
     * @param array $options
     * @param $type
     * @throws Exception
     */
    public function __construct(array $options, $type)
    {
        parent::__construct($type, $options['UnitID'], $options['UnitLabel'], $options['DeviceID'], $options['Options']);
        $this->valueTable = $options['ValueTableID'];
        $this->delta = (float)$options['Delta'];
    }

    /**
     * @return int|mixed
     */
    public function getValueTable()
    {
        return $this->valueTable;
    }

    /**
     * Получить показания за интервал
     * @param $dateFrom - с даты, может быть задана относительно $dateTo, значениями day|week|month,
     *                    значение по умолчанию day
     * @param $dateTo - по дату, если не задана, то берется текущий момент
     * @param $dataFormat - формат возвращаемой даты, значение по умолчанию '%H';
     * @param $descSort
     * @return array|null
     */
    public function getValuesForInterval($dateFrom = 'day', $dateTo = null, $dataFormat = '%H', $descSort = false)
    {

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
            $dataFormatQuery = 'DATE_FORMAT(Date, \'' . $dataFormat . '\')';
        } elseif ($dateFrom == 'week' || $dateFrom == 'month') {
            $dataFormatQuery = "DATE_FORMAT(Date, '%d.%m')";
        } else {
            $dataFormatQuery = "DATE_FORMAT(Date, '%H')";
        }

        if (empty($dateFrom) || $dateFrom == 'day') {
            $dateFromQuery = "($dateToQuery - INTERVAL 1 DAY)";
        } elseif ($dateFrom == 'week') {
            $dateFromQuery = "($dateToQuery - INTERVAL 7 DAY)";
        } elseif ($dateFrom == 'month') {
            $dateFromQuery = "($dateToQuery - INTERVAL 1 MONTH)";
        }

        $id = $this->getId();
        $nameTabValue = 'tvalue_' . $this->valueTable;

        $sortQuery = '';
        if ($descSort) {
            $sortQuery = ' DESC';
        }

        $query = 'SELECT Value,' . $dataFormatQuery . ' Date_f FROM ' . $nameTabValue . ' WHERE UnitID=' . $id .
            ' AND Date>=' . $dateFromQuery . ' AND Date<=' . $dateToQuery . ' ORDER BY Date' . $sortQuery;

        $result = null;
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
    public function getValueAverageForInterval($date = null, $intervalHour = null)
    {

        //исходная дата
        if (empty($date)) {
            $date1 = 'NOW()';
        } else {
            $date1 = '\'' . $date . '\'';
        }

        //интервал
        if (!is_int($intervalHour) || $intervalHour == 0) {
            $intervalHour = -1;
        }
        $date2 = '(' . $date1 . ' ' . ($intervalHour > 0 ? '+ ' : '- ') . 'INTERVAL ' . abs($intervalHour) . ' HOUR)';

        if ($intervalHour > 0) {
            $dateFrom = $date1;
            $dateTo = $date2;
        } else {
            $dateFrom = $date2;
            $dateTo = $date1;
        }

        $id = $this->getId();
        $nameTabValue = 'tvalue_' . $this->getValueTable();

        /** @noinspection SqlResolve */
        $query = 'SELECT avg(Value) AS Value FROM ' . $nameTabValue . ' WHERE UnitID=' . $id . ' AND Date>=' . $dateFrom . ' AND Date<=' . $dateTo;

        $result = null;
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

    /**
     * @return float
     */
    public function getDelta()
    {
        return (float)($this->delta);
    }

    /**
     * Получает данные устройства модуля скорректированные на delta модуля
     * @return false|string
     */
    public function getData()
    {
        $data = parent::getData();

        if ($data instanceof iDeviceDataValue) {
            $delta = $this->getDelta();
            $data->changeValue($delta);
        }

        return $data;
    }
}

abstract class moduleUnit extends unit implements iModuleUnite
{
    /**
     * moduleUnit constructor.
     * @param array $options
     * @param $type
     * @throws Exception
     */
    public function __construct(array $options, $type, $options_ = null)
    {
//        $sel = new selectOption();
//        $sel->set('DeviceID', $options['DeviceID']);
//        $arr = DB::getListDevices($sel);
//        parent::__construct($type, $options['UnitID'], $options['UnitLabel'], $arr[0]);
        parent::__construct($type, $options['UnitID'], $options['UnitLabel'], $options['DeviceID'], $options_);

    }


    /**
     * Записывает в устройство модуля данные
     * @param $data - строка в json формате
     * @return mixed
     */
    abstract function setData($data);
}

/**
 * Датчик температуры
 * Class temperatureUnit
 */
class temperatureUnit extends sensorUnit
{

    /**
     * temperatureUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::TEMPERATURE);
    }

}

class humidityUnit extends sensorUnit
{

    /**
     * temperatureUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::HUMIDITY);
    }

}

class pressureUnit extends sensorUnit
{

    /**
     * pressureUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::PRESSURE);
    }

}

class keyInUnit extends sensorUnit
{

    /**
     * keyInUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::KEY_IN);
    }

}

//Силовой ключ
class KeyOutUnit extends moduleUnit
{

    /**
     * powerKeyUnit constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::POWER_KEY);
    }

    function setData($data)
    {
        $device = $this->getDevice();
        if ($device instanceof iMakerDevice) {
            $device->setData($data);
        }
    }
}

class kitchenVentUnit extends sensorUnit
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::KITCHEN_HOOD);
    }

}

class gasSensorUnit extends sensorUnit
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::GAS_SENSOR);
    }

}

class boilerOpenThermUnit extends sensorUnit
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeUnit::BOILER_OPEN_THERM);
    }

}

class boilerPID extends moduleUnit
{

    private function getDefaultOptions()
    {
        $default = new stdClass();
        $default->b_kp = 1;
        $default->b_ki = 0.1;
        $default->b_kd = 10;
        $default->b_tar = 23;
        $default->b_cur = 1;
        $default->b_dK = 1;
        $default->b_dT = 1;
        $default->f_kp = 1;
        $default->b_tar1 = 20;
        $default->b_cur1 = 1;
        $default->b_dK1 = 1;
        $default->b_dT1 = 1;
        $default->f_kp1 = 1;
        $default->f_ki = 0.1;
        $default->f_kd = 10;
        $default->f_tar = 20;
        $default->f_cur = 0.4;
        $default->f_dK = 1;
        $default->f_dT = 1;
        $default->b_tIn = '';
        $default->b_tIn1 = '';
        $default->b_tfIn = '';
        $default->b_tfIn1 = '';
        $default->f_mode = 0;
        $default->b_tOut = '';
        $default->b_tOut1 = '';
        return $default;
    }

    public function __construct(array $options)
    {
        $options_ = new uniteOptionsJSON($options['UnitID'], $options['Options'], $this->getDefaultOptions());
        parent::__construct($options, typeUnit::BOILER_PID, $options_);
    }

    function setData($data)
    {
    }
}