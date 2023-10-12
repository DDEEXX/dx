<?php

require_once(dirname(__FILE__) . '/globalConst.interface.php');
require_once(dirname(__FILE__) . '/sqlDataBase.class.php');
require_once(dirname(__FILE__) . '/i2c.class.php');
require_once(dirname(__FILE__) . '/logger.class.php');
require_once(dirname(__FILE__) . '/sharedMemory.class.php');
require_once(dirname(__FILE__) . '/mqtt.class.php');
require_once dirname(__FILE__) . '/ownet.php';

/*Форматированные данные с датчиков*/
class formatDeviceValue implements iDeviceDataValue
{
    public $date = 0;
    public $value = null;
    public $status = 0;
    public $valueNull = true;

    public function getDataJSON()
    {
        return json_encode(
            ['value' => $this->value,
                'valueNull' => $this->valueNull,
                'date' => $this->date,
                'status' => $this->status]);
    }

    function setDefaultValue()
    {
        $this->value = 0.0;
        $this->valueNull = true;
        $this->date = 0;
        $this->status = 0;
    }
}

interface iFormatterValue {
    function formatRawValue($value);
}

/*Данные физического датчика*/
interface iDeviceValue
{
    function setValue($value);

    function getStorageValue();

    function getFormatValue();
}

abstract class aDeviceValue implements iDeviceValue
{
    protected $id;
    protected $formatter;

    /**
     * @param $id
     * @param $formatter - объект для форматирования "сырых" данных датчиков в единый формат для dxHome
     */
    public function __construct($id, $formatter)
    {
        $this->id = $id;
        $this->formatter = $formatter;
    }

    abstract protected function getValue();

    function getStorageValue()
    {
        $result = storageValues::SHARED_MEMORY;
        if (is_a($this, 'aDeviceValueDB')) $result = storageValues::DATA_BASE;
        return $result;
    }
}

abstract class aDeviceValueSM extends aDeviceValue
{

    protected function getValue()
    {
        // TODO: Implement getValue() method.
    }

    function setValue($value)
    {
        // TODO: Implement setValue() method.
    }

}

abstract class aDeviceValueDB extends aDeviceValue
{

    /**
     * Получение "сырых" данных из базы
     * @return array|null
     */
    protected function getValue()
    {
        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getConst. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }
        $result = null;
        $deviceID = $con->getConnect()->real_escape_string($this->id);
        $query = sprintf('SELECT * FROM tdevicevalue WHERE DeviceID=%s Order By Date Desc LIMIT 1', $deviceID);
        try {
            $value = queryDataBase::getOne($con, $query);
            if (is_array($value) && array_key_exists('Value', $value)) {
                $result = [];
                $result['date'] = strtotime($value['Date']);
                $result['value'] = $value['Value'];
            }
        } catch (querySelectDBException $e) {
        }
        return $result;
    }

    function setValue($value)
    {
        $dateValue = date('Y-m-d H:i:s');
        $currentData = $this->getValue();
        $insertData = !is_array($currentData);

        if ($insertData) {
            $query = sprintf('INSERT INTO tdevicevalue (DeviceID, Date, Value) VALUES (\'%s\', \'%s\', \'%s\')',
                $this->id, $dateValue, $value);
        } else {
            $query = sprintf('UPDATE tdevicevalue SET Date = \'%s\', Value = \'%s\' WHERE DeviceID = %s',
                $dateValue, $value, $this->id);
        }

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

    function getFormatValue()
    {
        $valueData = $this->getValue();
        return $this->formatter->formatRawValue($valueData);
    }

}

/**
 *  Значения данных датчика
 */
interface iDeviceDataValue
{
    function getDataJSON();

    function setDefaultValue();
}

class deviceDataValue implements iDeviceDataValue
{

    public $value;
    public $valueNull;
    public $date;
    public $status;

    /** Вернуть данные в виде JSON строки
     * @return false|string
     */
    public function getDataJSON()
    {
        return json_encode(
            ['value' => $this->value,
                'valueNull' => $this->valueNull,
                'date' => $this->date,
                'status' => $this->status]);
    }

    function setDefaultValue()
    {
        $this->value = 0.0;
        $this->valueNull = true;
        $this->date = 0;
        $this->status = 0;
    }
}

/**
 * Данные датчика (хранятся в sm)
 */
interface iDeviceData
{
    function setData($value, $date, $valueNull, $status);

    function updateData($value, $date, $valueNull, $status);

    function getData();
}

class deviceData implements iDeviceData
{

    /** @var int|null - id устройства, для идентификации в sm */
    private $id;
    /** @var deviceDataValue - значения данных */
    private $data;

    /**
     * @param $id - id устройства (device)
     */
    public function __construct($id = null)
    {
        $this->id = is_null($id) ? $id : (int)$id;
        $this->data = new deviceDataValue();
    }

    private function writeDataDefaultValues()
    {
        $this->data->setDefaultValue();
    }

    /**
     * Записать ValueNull в 9-ый бит status
     * @return int
     */
    private function getStatusSmFormat()
    {
        $valueNull = $this->data->valueNull ? bindec('100000000') : 0;
        return $this->data->status | $valueNull;
    }

    private function convertCurrentDataToSmFormat()
    {
        $data = $this->data;
        $result = [];
        $result['value'] = $data->value;
        $result['date'] = $data->date;
        $result['status'] = $this->getStatusSmFormat();
        return $result;
    }

    function setData($value = 0.0, $date = 0, $valueNull = true, $status = 0)
    {
        $data = $this->data;
        if (is_numeric($value)) {
            $data->value = (float)$value;
        } else {
            $data->value = 0.0;
        }

        if (is_int($date)) {
            if ($date == 0) {
                $data->date = time();
            } else {
                $data->date = $date;
            }
        } else {
            $data->date = time();
        }

        if (is_bool($valueNull)) {
            $data->valueNull = $valueNull;
        } else {
            $data->valueNull = false;
        }

        if (is_int($status)) {
            $data->status = $status;
        } else {
            $data->status = 0;
        }

        $result = false;
        if (is_int($this->id)) {
            $dataSm = $this->convertCurrentDataToSmFormat();
            $result = sharedMemoryDeviceData::set($this->id, $dataSm);
        }

        return $result;
    }

    private function getDataFromSm()
    {
        if (!is_int($this->id)) {
            return null;
        }
        $sm = sharedMemoryUnits::getInstance(sharedMemory::PROJECT_LETTER_DATA_DEVICE, sharedMemory::SIZE_MEMORY_DATA_DEVICE);
        if (!is_null($sm)) {
            return $sm->get($this->id);
        } else {
            return null;
        }

    }

    /**
     * Записывает в текущие данные статус из sm. В 9 бите храниться ValueNull, в последних 8 битах status
     * @param $smStatus - статус из sm
     * @return void
     */
    private function writeSmStatusToCurrentData($smStatus)
    {
        $this->data->valueNull = (bool)($smStatus & bindec('100000000'));
        $this->data->status = (int)$smStatus & bindec('11111111');
    }

    private function extractDataFromSm()
    {
        $this->writeDataDefaultValues();
        $dataSm = $this->getDataFromSm();
        if (is_array($dataSm)) {
            $data = $this->data;
            if (array_key_exists('value', $dataSm)) {
                $data->value = (float)$dataSm['value'];
            }
            if (array_key_exists('date', $dataSm)) {
                $data->date = (int)$dataSm['date'];
            }
            if (array_key_exists('status', $dataSm)) {
                $status = (int)$dataSm['status'];
                $this->writeSmStatusToCurrentData($status);
            }
        }
    }

    function getData()
    {
        $this->extractDataFromSm();
        return $this->data;
    }

    /**
     * Обновляет данные в sm. Записывает данные в sm если они отличаются от текущего значения
     * @return void
     */
    function updateData($value = 0.0, $date = 0, $valueNull = true, $status = 0)
    {
        $this->extractDataFromSm();
        if (($this->data->value != $value) || ($this->data->valueNull != $valueNull)) {
            $this->setData($value, $date, $valueNull, $status);
        }
    }
}

/**
 * Физическое устройство
 */
interface iDevicePhysic
{
    function test();

    function getFormatValue();

    function getData($deviceID);

    function getStorageValue();

    function isValue();

    function setValue($value);
}

interface iDeviceSensorPhysic extends iDevicePhysic
{

    /**
     * Запрос данных с физического датчика
     * @param $ignoreActivity - если false, то отправляем запрос, если датчик не присылает данные самостоятельно
     * @return mixed
     */
    function requestData($ignoreActivity);
}

interface iDeviceMakerPhysic extends iDevicePhysic
{
    function setData($data);
}

interface iDevicePhysicMQTT
{
    const DEFAULT_TEST_PAYLOAD = 'test';

    function getTopicStat();

    function getTopicTest();

    function getTopicSet();

    function formatTestPayload($testPayload, $ignoreUnknown = false);
}

/*устройство способное отправлять по MQTT данные о "тревоге" на этом устройстве*/
interface iDeviceAlarm
{
    function getTopicAlarm();

    function onMessageAlarm($payload);
}

/*отправляет по MQTT, сведения о "тревоге"*/
interface iAlarmMQTT
{
    function getTopicAlarm();

    function alarm($payload);

    function saveInJournal($device, $payload);
}

abstract class aAlarmMQTT implements iAlarmMQTT
{

    private $topicAlarm;

    public function __construct($topic)
    {
        $this->topicAlarm = $topic;
    }

    public function getTopicAlarm()
    {
        return $this->topicAlarm;
    }

    abstract public function alarm($payload);

    abstract function convertPayload($payload);

    private function getLastDataInJournal($device, $currentData)
    {
        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции aSensorDevice::getAlarmData. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return null;
        }
        $deviceID = $con->getConnect()->real_escape_string($device->getDeviceID());
        $query = sprintf('SELECT * FROM tdevicealarm WHERE DeviceID = %s AND Date < \'%s\' Order By Date Desc LIMIT 1',
            $deviceID, $currentData);
        $result = null;
        try {
            $value = queryDataBase::getOne($con, $query);
            if (is_array($value) && array_key_exists('Value', $value)) {
                $result['date'] = strtotime($value['Date']);
                $result['value'] = $value['Value'];
            }
        } catch (querySelectDBException $e) {
        }
        return $result;
    }

    private function savePayloadInJournal($device, $payload)
    {
        $currentData = date('Y-m-d H:i:s');

        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции aAlarmMQTT::savePayloadInJournal. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return;
        }

        $deviceID = $con->getConnect()->real_escape_string($device->getDeviceID());
        $query = sprintf('SELECT * FROM tdevicealarm WHERE DeviceID = %s AND Date = \'%s\'',
            $deviceID, $currentData);
        try {
            $value = queryDataBase::getOne($con, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при обращении к базе данных',
                loggerTypeMessage::ERROR, loggerName::ERROR);
            return;
        }

        if (is_null($value)) {
            $query = sprintf('INSERT INTO tdevicealarm (DeviceID, Date, Value) VALUES (%s, \'%s\', \'%s\')',
                $deviceID, $currentData, trim($payload));
        } else {
            $query = sprintf('UPDATE tdevicealarm SET Value = \'%s\' WHERE Date = \'%s\' AND DeviceID = %s',
                trim($payload), $currentData, $deviceID);
        }

        try {
            $result = queryDataBase::execute($con, $query);
            if (!$result) {
                logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                    loggerTypeMessage::ERROR, loggerName::ERROR);
            }
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при добавлении данных в базу данных',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }
    }

    /** Запись в журнал срабатывания тревоги данных устройств
     * @param $device - устройство на котором сработала тревога
     * @param $payload - входящее сообщение
     * и текущем сообщением, если флаг ложь, то запись в журнал происходит всегда
     * @return void
     */
    public function saveInJournal($device, $payload)
    {
        if (!strlen(trim($payload))) { //если пришла пустая строка
            return;
        }
        $this->savePayloadInJournal($device, $payload);
    }

}

interface iDevicePhysicOWire
{
    function getAddress();
}

interface iDeviceSensorPhysicOWire extends iDeviceSensorPhysic, iDevicePhysicOWire
{
    function getAlarm();

    function updateAlarm();
}

interface iDeviceMakerPhysicOWire extends iDeviceMakerPhysic, iDevicePhysicOWire
{
    function getChanel();
}

abstract class aDevicePhysic implements iDevicePhysic
{
    protected $formatValue = formatValueDevice::NO_FORMAT;
    protected $value = null;

    public function getFormatValue()
    {
        return $this->formatValue;
    }

    /**
     * Получает уже запрошенные и записанные (sm|db) данные с датчика
     * @param $deviceID
     * @return array|false|string
     */
    function getData($deviceID)
    {
        //новый механизм
        if (!is_null($this->value)) {
            return  $this->value->getFormatValue();
        }

        switch ($this->formatValue) {
            case formatValueDevice::MQTT_KITCHEN_HOOD:
            case formatValueDevice::MQTT_GAS_SENSOR:

                try {
                    $con = sqlDataBase::Connect();
                } catch (connectDBException $e) {
                    logger::writeLog('Ошибка при подключении к базе данных в функции DB::getConst. ' . $e->getMessage(),
                        loggerTypeMessage::FATAL, loggerName::ERROR);
                    return '';
                }
                $deviceID = $con->getConnect()->real_escape_string($deviceID);
                $query = 'SELECT * FROM tdevicevalue WHERE DeviceID=' . $deviceID . ' Order By Date Desc LIMIT 1';
                $result = [];
                try {
                    $value = queryDataBase::getOne($con, $query);
                    if (is_array($value) && array_key_exists('Value', $value)) {
                        $result['date'] = strtotime($value['Date']);
                        $result['value'] = $value['Value'];
                    } else {
                        $result['date'] = 0;
                        $result['value'] = '';
                    }
                } catch (querySelectDBException $e) {
                    $result['date'] = 0;
                    $result['value'] = '';
                }
                return $result;
            default :
                $deviceData = new deviceData($deviceID);
                return $deviceData->getData();
        }
    }

    function getStorageValue()
    {
        return $this->value instanceof iDeviceValue ? $this->value->getStorageValue() : storageValues::SHARED_MEMORY;
    }

    /**
     * Проверка на существования объекта в value
     * @return bool
     */
    public function isValue()
    {
        return $this->value instanceof iDeviceValue;
    }

    function setValue($value)
    {
        if (!is_null($this->value)) $this->value->setValue($value);
    }
}

abstract class aDeviceSensorPhysic extends aDevicePhysic implements iDeviceSensorPhysic
{
    abstract function requestData($ignoreActivity);
}

abstract class aDeviceMakerPhysic extends aDevicePhysic implements iDeviceMakerPhysic
{
    abstract function setData($data);
}

abstract class aDeviceSensorPhysicMQTT extends aDeviceSensorPhysic implements iDevicePhysicMQTT
{
    private $topicCmnd;  //топик для подачи команд устройству
    private $topicStat;  //топик на который приходит информация с устройства
    private $topicAvailabilityInput; //топик для подачи команды тестирования модуля (нахождение в сети)
    private $topicTest; //топик на который приходит информация о нахождении устройства в сети
    private $topicSet;

    private $requestPayload; //сообщение для запроса данных с датчика
    private $testPayload;
    protected $selfActivity = false; //

    public function __construct($mqttParameters, $formatValue = formatValueDevice::NO_FORMAT)
    {
        $this->topicCmnd = $mqttParameters['topicCmnd'];
        $this->topicStat = $mqttParameters['topicStat'];
        $this->topicTest = $mqttParameters['topicTest'];
        $this->topicAvailabilityInput = isset($mqttParameters['topicAvailability']) ?
            $mqttParameters['topicAvailability'] : $mqttParameters['topicCmnd'];
        $this->requestPayload = isset($mqttParameters['payloadRequest']) ? $mqttParameters['payloadRequest'] : '';
        $this->topicSet = isset($mqttParameters['topicSet']) ?
            $mqttParameters['topicSet'] : '';
        $this->testPayload = isset($mqttParameters['testPayload']) ?
            $mqttParameters['testPayload'] : iDevicePhysicMQTT::DEFAULT_TEST_PAYLOAD;

        $this->formatValue = $formatValue;
    }

    private function publishTopic($payload)
    {
        if (is_null($this->topicCmnd)) return;
        if (trim($payload) == '') return;
        $mqtt = mqttSend::connect();
        $mqtt->publish($this->topicCmnd, $payload);
    }

    function requestData($ignoreActivity = true)
    {
        if (!$ignoreActivity && $this->selfActivity) return null;

        $this->publishTopic($this->requestPayload);
        return null;
    }

    public function getTopicStat()
    {
        return $this->topicStat;
    }

    public function test()
    {
        if (!empty($this->topicAvailabilityInput)) {
            $mqtt = mqttSend::connect();
            $mqtt->publish($this->topicAvailabilityInput, $this->testPayload);
            unset($mqtt);
        }
        return testDeviceCode::IS_MQTT_DEVICE;
    }

    function getTopicTest()
    {
        return trim($this->topicTest);
    }

    public function formatTestPayload($testPayload, $ignoreUnknown = false)
    {
        $testCode = is_numeric($testPayload) ? (int)$testPayload : testDeviceCode::UNKNOWN;
        return ($testCode == testDeviceCode::UNKNOWN && $ignoreUnknown) ? null : $testCode;
    }

    function getTopicSet()
    {
        return trim($this->topicSet);
    }
}

abstract class aDeviceMakerPhysicMQTT extends aDeviceMakerPhysic implements iDevicePhysicMQTT
{

    private $topicCmnd;
    private $topicStat;
    private $topicAvailabilityInput; //топик для подачи команды тестирования модуля (нахождение в сети)
    private $topicTest;
    private $topicSet;

    protected $testPayload;

    public function __construct($mqttParameters, $formatValue = formatValueDevice::NO_FORMAT)
    {
        $this->topicCmnd = $mqttParameters['topicCmnd'];
        $this->topicStat = $mqttParameters['topicStat'];
        $this->topicTest = $mqttParameters['topicTest'];
        $this->topicAvailabilityInput = isset($mqttParameters['topicAvailability']) ?
            $mqttParameters['topicAvailability'] : $mqttParameters['topicCmnd'];
        $this->testPayload = isset($mqttParameters['testPayload']) ?
            $mqttParameters['testPayload'] : iDevicePhysicMQTT::DEFAULT_TEST_PAYLOAD;
        $this->topicSet = isset($mqttParameters['topicSet']) ?
            $mqttParameters['topicSet'] : '';
        $this->formatValue = $formatValue;
    }

    function setData($data)
    {
        try {
            if (is_string($data)) {
                $mqtt = mqttSend::connect();
                $mqtt->publish($this->topicCmnd, $data);
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getTopicStat()
    {
        return $this->topicStat;
    }

    public function test()
    {
        if (!empty($this->topicAvailabilityInput)) {
            $mqtt = mqttSend::connect();
            $mqtt->publish($this->topicAvailabilityInput, $this->testPayload);
            unset($mqtt);
        }
        return testDeviceCode::IS_MQTT_DEVICE;
    }

    function getTopicTest()
    {
        return trim($this->topicTest);
    }

    public function formatTestPayload($testPayload, $ignoreUnknown = false)
    {
        $testCode = is_numeric($testPayload) ? (int)$testPayload : testDeviceCode::UNKNOWN;
        return ($testCode == testDeviceCode::UNKNOWN && $ignoreUnknown) ? null : $testCode;
    }

    function getTopicSet()
    {
        return trim($this->topicSet);
    }
}

abstract class aDeviceSensorPhysicOWire extends aDeviceSensorPhysic implements iDeviceSensorPhysicOWire
{

    private $address;
    private $alarm;

    /**
     * @param $address
     * @param $alarm
     */
    public function __construct($address, $alarm)
    {
        $this->address = $address;
        $this->alarm = $alarm;
    }

    /**
     * @return string
     */
    function getAddress()
    {
        if (preg_match('/^[A-F0-9]{2,}\.[A-F0-9]{12,}/', $this->address)) {
            return $this->address;
        } else {
            return '';
        }
    }

    /**
     *  Устанавливает set_alarm у физического датчика в соответствии со свойством alarm
     */
    function updateAlarm()
    {
        $result = false;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $address = $this->getAddress();
        $ow = new OWNet($OWNetAddress);
        if (preg_match('/^[A-F0-9]{2,}\.[A-F0-9]{12,}/', $address)) { //это датчик OWire
            $result = $ow->set('/' . $address . '/set_alarm', $this->alarm);
            unset($ow);
        }
        if (!$result) {
            logger::writeLog('Ошибка установки set_alarm у датчика :: ' . $address,
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }
    }

    /**
     * @return mixed
     */
    public function getAlarm()
    {
        return $this->alarm;
    }

}

abstract class aDeviceMakerPhysicOWire extends aDeviceMakerPhysic implements iDeviceMakerPhysicOWire
{

    private $address;
    private $chanel;

    /**
     * @param $address
     * @param null $chanel
     */
    public function __construct($address, $chanel = null)
    {
        $this->address = $address;
        $this->chanel = is_null($chanel) ? '' : $chanel;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        if (preg_match('/^[A-F0-9]{2,}\.[A-F0-9]{12,}/', $this->address)) {
            return $this->address;
        } else {
            return '';
        }
    }

    /**
     * @return mixed|null
     */
    public function getChanel()
    {
        return $this->chanel;
    }

}

class DeviceSensorPhysicDefault extends aDeviceSensorPhysic
{
    function requestData($ignoreActivity = true)
    {
        // TODO: Implement requestData() method.
    }

    function test()
    {
        // TODO: Implement test() method.
    }
}

class DeviceMakerPhysicDefault extends aDeviceMakerPhysic
{
    function setData($data)
    {
        return true;
    }

    function test()
    {
        return 0;
    }
}

interface iDevice
{
    function getDeviceID();

    function getNote();

    function getNet();

    function getType();

    function getDisabled();

    function getDeviceFormatValue();

    function getDevicePhysic();

    /**
     * Получить данные датчика из sm памяти
     * @return false|string - результат в виде JSON строки
     */
    function getData();

    function test();

    /**
     * Получить место хранение значений датчиков
     * @return mixed
     */
    function getStorageValue();
}

interface iSensorDevice extends iDevice
{
    function requestData($ignoreActivity);
}

interface iMakerDevice extends iDevice
{
    function setData($data);
}

abstract class aDevice implements iDevice
{
    private $net;
    private $type;
    private $deviceID;
    private $disabled;
    private $note;

    protected $devicePhysic = null;

    public function __construct($deviceID, $net, $type, $disabled, $note)
    {
        $this->net = $net;
        $this->type = $type;
        $this->deviceID = $deviceID;
        $this->disabled = $disabled;
        $this->note = $note;
    }

    public function getDeviceID()
    {
        return $this->deviceID;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function getNet()
    {
        return $this->net;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDisabled()
    {
        return $this->disabled;
    }

    function getDeviceFormatValue()
    {
        return $this->devicePhysic->getFormatValue();
    }

    function getDevicePhysic()
    {
        return $this->devicePhysic;
    }

    function getData()
    {
        if ($this->devicePhysic instanceof iDevicePhysic) {
            $data = $this->devicePhysic->getData($this->getDeviceID());
        } else {
            $value = new deviceDataValue();
            $value->setDefaultValue();
            $data = $value->getDataJSON();
        }
        return $data;
    }

    function test()
    {
        $result = testDeviceCode::NO_DEVICE;
        if ($this->devicePhysic instanceof iDevicePhysic) {
            if ($this->getDisabled()) {
                return testDeviceCode::DISABLED;
            }
            $result = $this->devicePhysic->test();
        }
        return $result;
    }

    function getStorageValue()
    {
        return $this->devicePhysic->getStorageValue();
    }
}

/** Устройство датчик*/
abstract class aSensorDevice extends aDevice implements iSensorDevice
{
    /**
     * sensor constructor.
     * @param array $options
     * @param $typeDevice
     */
    public function __construct(array $options, $typeDevice)
    {
        $deviceID = intval($options['DeviceID']);
        $net = $options['NetTypeID'];
        $disabled = $options['Disabled'];
        $note = $options['Note'];
        parent::__construct($deviceID, $net, $typeDevice, $disabled, $note);
        $this->devicePhysic = new DeviceSensorPhysicDefault();
    }

    /**
     * Записывает в базу данных данные с датчика (как правило, в формате json,
     * такие данные не нужны для "быстрого" анализа).
     * @param $value - записываемые данные
     * @return void
     */
    public function saveValue($value)
    {

        $dateValue = date('Y-m-d H:i:s');
        $deviceID = $this->getDeviceID();

        $insertData = false;
        $currentData = parent::getData();
        if (is_array($currentData)) {
            $insertData = $currentData['value'] == '';
        } else if ($currentData == '') {
            $insertData = true;
        }

        if ($insertData) {
            $query = sprintf('INSERT INTO tdevicevalue (DeviceID, Date, Value) VALUES (\'%s\', \'%s\', \'%s\')',
                $deviceID, $dateValue, $value);
        } else {
            $template = 'UPDATE tdevicevalue SET Date = \'%s\', Value = \'%s\' WHERE DeviceID = %s';
            $query = sprintf($template, $dateValue, $value, $deviceID);
        }

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

    abstract function requestData($ignoreActivity = true);
}

/** Устройство исполнитель*/
abstract class aMakerDevice extends aDevice implements iMakerDevice
{

    /**
     * maker constructor.
     * @param array $options
     * @param $typeDevice
     */
    public function __construct(array $options, $typeDevice)
    {
        $deviceID = intval($options['DeviceID']);
        $net = $options['NetTypeID'];
        $disabled = $options['Disabled'];
        $note = $options['Note'];
        parent::__construct($deviceID, $net, $typeDevice, $disabled, $note);
        $this->devicePhysic = new DeviceMakerPhysicDefault();
    }

    function setData($data)
    {
        if ($this->devicePhysic instanceof iDeviceMakerPhysic) {
            return $this->devicePhysic->setData($data);
        }
        return false;
    }

}

require_once dirname(__FILE__) . '/devices/temperature.device.class.php';
require_once dirname(__FILE__) . '/devices/humidity.device.class.php';
require_once dirname(__FILE__) . '/devices/pressure.device.class.php';
require_once dirname(__FILE__) . '/devices/keyIn.device.class.php';
require_once dirname(__FILE__) . '/devices/keyOut.device.class.php';
require_once dirname(__FILE__) . '/devices/zigbeeSwitchWHD02.device.class.php';
require_once dirname(__FILE__) . '/devices/kitchenHood.device.class.php';
require_once dirname(__FILE__) . '/devices/gasSensor.device.class.php';

class labelSensorDevice extends aSensorDevice
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::LABEL);
    }

    function requestData($ignoreActivity = true)
    {
        // TODO: Implement requestData() method.
    }

}