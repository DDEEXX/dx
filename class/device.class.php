<?php

require_once(dirname(__FILE__) . '/globalConst.interface.php');
require_once(dirname(__FILE__) . '/sqlDataBase.class.php');
require_once(dirname(__FILE__) . '/i2c.class.php');
require_once(dirname(__FILE__) . '/logger.class.php');
require_once(dirname(__FILE__) . '/sharedMemory.class.php');
require_once(dirname(__FILE__) . '/mqtt.class.php');
require_once dirname(__FILE__) . '/ownet.php';

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

    public function getDataJSON()
    {
        return json_encode(['value' => $this->value,
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
        $this->id = is_null($id) ? null : (int)$id;
        $this->data = new deviceDataValue();
    }

    private function writeDataDefaultValues()
    {
        $this->data->setDefaultValue();
    }

    /** Записать ValueNull в 9 бит status
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
        return $sm->get($this->id);
    }

    /** Записывает в текущие данные статус из sm. В 9 бите храниться ValueNull, в последних 8 битах status
     * @param $smStatus - статус из sm
     * @return void
     */
    private function writeSmStatusToCurrentData($smStatus)
    {
        $this->data->valueNull = (bool)($smStatus & bindec('100000000'));
        $this->data->status = (int)($smStatus & bindec('11111111'));
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


    /** Обновляет данные в sm. Записывает данные в sm если они отличаются от текущего значения
     * @return void
     */
    function updateData($value = 0.0, $date = 0, $valueNull = true, $status = 0)
    {
        $this->extractDataFromSm();
        if (($this->data->value != $value) || ($this->data->valueNull!=$valueNull)) {
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
}

interface iDeviceSensorPhysic extends iDevicePhysic
{
    function requestData();
}

interface iDeviceMakerPhysic extends iDevicePhysic{
    function getStatus();
    function setData($data);
}

interface iDeviceSensorPhysicOWire extends iDeviceSensorPhysic {
    function getAddress();
    function updateAlarm();
}

abstract class aDevicePhysic implements iDevicePhysic
{
    protected $formatValue = formatValueDevice::NO_FORMAT;
    public function getFormatValue()
    {
        return $this->formatValue;
    }
    abstract function test();
}

abstract class aDeviceSensorPhysic extends aDevicePhysic implements iDeviceSensorPhysic
{
    abstract function requestData();
}

abstract class aDeviceSensorPhysicMQTT extends aDeviceSensorPhysic
{
    private $topic;
    private $requestPayload;

    public function __construct($topic, $requestPayload, $formatValue = formatValueDevice::NO_FORMAT)
    {
        $this->topic = $topic;
        $this->requestPayload = $requestPayload;
        $this->formatValue = $formatValue;
    }

    private function publishTopic($payload)
    {
        if (is_null($this->topic)) return;
        $mqtt = mqttSend::connect();
        $mqtt->publish($this->topic, $payload);
    }

    function requestData()
    {
        $this->publishTopic($this->requestPayload);
        return null;
    }

    function test()
    {
        $this->publishTopic('test');
    }
}

abstract class aDeviceSensorPhysicOWire extends aDeviceSensorPhysic implements iDeviceSensorPhysicOWire{

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
     * @return mixed
     */
    function getAddress()
    {
        return $this->address;
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

    function test()
    {
        $result = testUnitCode::NO_CONNECTION;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $address = $this->getAddress();
        if (preg_match('/^[A-F0-9]{2,}\.[A-F0-9]{12,}/', $address)) { //это датчик OWire
            $ow = new OWNet($OWNetAddress);
            for ($i = 0; $i < 5; $i++) {
                $temperature = $ow->get($address . '/temperature12');
                if (!is_null($temperature)) {
                    $result = testUnitCode::WORKING;
                    break;
                }
            }
        } else {
            $result = testUnitCode::ONE_WIRE_ADDRESS;
        }
        return $result;
    }

}

class DeviceSensorPhysicDefault extends aDeviceSensorPhysic
{
    function requestData()
    {
        // TODO: Implement requestData() method.
    }

    function test() {}
}

abstract class aDeviceMakerPhysic extends aDevicePhysic implements iDeviceMakerPhysic {

}

abstract class aDeviceMakerPhysicOWire extends aDeviceMakerPhysic {

    private $address;

    /**
     * @param $address
     */
    public function __construct($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

}

abstract class aDeviceMakerPhysicMQTT extends aDeviceMakerPhysic {

    private $topicCmnd;
    private $topicStat;

    public function __construct($topicCmnd, $topicStat, $formatValue = formatValueDevice::NO_FORMAT)
    {
        $this->topicCmnd = $topicCmnd;
        $this->topicStat = $topicStat;
        $this->formatValue = $formatValue;
    }

    function test()
    {
        // TODO: Implement test() method.
    }

    function setData($data)
    {
        $result = true;
        try {
            if (is_string($data)) {
                $mqtt = mqttSend::connect();
                $mqtt->publish($this->topicCmnd, $data);
            } else {
                return false;
            }
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }
}

class DeviceMakerPhysicDefault extends aDeviceMakerPhysic {


    function getStatus()
    {
        return true;
    }

    function setData($data)
    {
        return true;
    }

    function test() {}
}

/**
 * Устройство
 */

interface iDevice
{
    function getDeviceID();

    function getNet();

    function getType();

    function getDisabled();

    function getDeviceFormatValue();

    function getDevicePhysic();

}

interface iSensorDevice extends iDevice {
    function requestData();
}

interface iMakerDevice extends iDevice {
    function getStatus();
    function setData($data);
}

/** Устройство */
abstract class aDevice implements iDevice
{
    private $net;
    private $type;
    private $deviceID;
    private $disabled;
    protected $devicePhysic;

    public function __construct($deviceID, $net, $type, $disabled)
    {
        $this->net = $net;
        $this->type = $type;
        $this->deviceID = $deviceID;
        $this->disabled = $disabled;
        $this->devicePhysic = new DeviceSensorPhysicDefault();
    }

    public function getDeviceID()
    {
        return $this->deviceID;
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

    public function addInBD()
    {
        try {
            $conn = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе (device.class.php) функция addInBD.' . $e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
            return false;
        }
        $adr = $conn->getConnect()->real_escape_string($this->address);
        $alarm = $conn->getConnect()->real_escape_string($this->alarm);

        $query = "INSERT tdevice (Address, NetTypeID, DeviceTypeID, Disabled, set_alarm) VALUES ('$adr', '$this->net', '$this->type', '$this->disabled', '$alarm')";

        try {
            return queryDataBase::execute($conn, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при выполнении sql запроса (device.class.php) функция addInBD.' . $e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
            return false;
        }
    }

    function getDeviceFormatValue()
    {
        return $this->devicePhysic->getFormatValue();
    }

    function getDevicePhysic()
    {
        return $this->devicePhysic;
    }
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
        parent::__construct($deviceID, $net, $typeDevice, $disabled);
    }
    abstract function getStatus();
    abstract function setData($data);

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
        parent::__construct($deviceID, $net, $typeDevice, $disabled);
    }

    private function getValueOWNet()
    {
        $result = null;
        //$OWNetAdress = DB::getConst('OWNetAddress');
        //$OWNetDir = DB::getConst('OWNETDir');

        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);

        $address = $this->getAddress();
        if (preg_match('/^28\./', $address)) { //это датчик DS18B20

            /*            $ow = new OWNet($OWNetAdress);
                        $tekValue = $ow->get('/uncached/' . $address . '/temperature12');

                        if (is_null($tekValue) || $tekValue == "0") { //если датчик не сработал попробуем еще один раз
                            sleep(1); //ждем 1 секунду
                            $tekValue = $ow->get('/uncached/' . $address . '/temperature12');
                        }
                        if (!is_null($tekValue)) { //если получили температуру, то возвращаем результат
                            $result = $tekValue;
                        }
                        else { //запишем в лог об ошибке
                            logger::writeLog('Ошибка получения температуры с датчика :: ' . $address, loggerTypeMessage::ERROR);
                        }

                        if (!is_null($tekValue)) { //иногда когда датчик не срабатывает, возвращает 0
                            if ($tekValue == 0) {
                                //т.е. 0 датчик никогда не вернет, но это очень редкая ситуация
                                //поэтому лучше без 0, чем провалы (т.е 15.0, 15.1, 15.2, 0 , 15.2, 15,3)
                                $result = null;
                            }
                        }

                        unset($ow);*/

            $f = file($OWNetDir . '/' . $address . '/temperature12');
            if ($f === false) { //попробуем еще раз
                usleep(500000); //ждем 0.5 секунд
                $f = file($OWNetDir . '/' . $address . '/temperature12');
            }

            if ($f === false) {
                logger::writeLog('Ошибка получения температуры с датчика :: ' . $address, loggerTypeMessage::ERROR);
                $result = null;
            } else {
                $result = $f[0];
            }

        } /*        elseif (preg_match("/^12\./", $address)) { //это датчик DS2406

            $ow = new OWNet($OWNetAdress);

            $tekValue = $ow->get('/uncached/' . $address . '/sensed.' . $chanel);
            if (is_null($tekValue)) {
                $tekValue = 1; //1 это нет
            }

            $result = $tekValue ? 0 : 1;

            unset($ow);

        }*/
        else {
            logger::writeLog('Неудачная попытка получить данные с датчика :: ' . $address, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    private function getValueI2C()
    {
        $result = null;
/*        $I2CBUS = DB::getConst('I2CBUS');
        $i2c_address = $this->getAddress();
        $model = $this->getModel();
        if ($model == 'BMP180') { //это датчик DS18B20
            if ($this->getType() == typeDevice::TEMPERATURE) {


                $ac5 = i2c::readUnShort($I2CBUS, $i2c_address, 0xB2);
                $ac6 = i2c::readUnShort($I2CBUS, $i2c_address, 0xB4);
                $mc = i2c::readShort($I2CBUS, $i2c_address, 0xBC);
                $md = i2c::readShort($I2CBUS, $i2c_address, 0xBE);

                // reading uncompensated temperature
                i2c::writeByte($I2CBUS, $i2c_address, 0xF4, 0x2E);
                usleep(4600); // Should be not less than 4500
                $msb = i2c::readByte($I2CBUS, $i2c_address, 0xF6);
                $lsb = i2c::readByte($I2CBUS, $i2c_address, 0xF7);
                $ut = $msb << 8 | $lsb;

                // calculating true temperature
                $x1 = (($ut - $ac6) * $ac5) / 32768;
                $x2 = ($mc * 2048) / ($x1 + $md);
                $b5 = $x1 + $x2;
                $result = ($b5 + 8) / 160;
            } elseif ($this->getType() == typeDevice::PRESSURE) {
                $oss = 1; // oversampling setting
                $sleep_time = array(
                    0 => 4600, // 4.5 ms according to documentation, but let's put a little bit more
                    1 => 7600, // 7.5 ms
                    2 => 13600, // 13.5 ms
                    3 => 25600 // 25.5 ms
                );

                $ac1 = i2c::readShort($I2CBUS, $i2c_address, 0xAA);
                $ac2 = i2c::readShort($I2CBUS, $i2c_address, 0xAC);
                $ac3 = i2c::readShort($I2CBUS, $i2c_address, 0xAE);
                $ac4 = i2c::readUnShort($I2CBUS, $i2c_address, 0xB0);
                $ac5 = i2c::readUnShort($I2CBUS, $i2c_address, 0xB2);
                $ac6 = i2c::readUnShort($I2CBUS, $i2c_address, 0xB4);
                $b1 = i2c::readShort($I2CBUS, $i2c_address, 0xB6);
                $b2 = i2c::readShort($I2CBUS, $i2c_address, 0xB8);
                $mc = i2c::readShort($I2CBUS, $i2c_address, 0xBC);
                $md = i2c::readShort($I2CBUS, $i2c_address, 0xBE);

                // reading uncompensated temperature
                i2c::writeByte($I2CBUS, $i2c_address, 0xF4, 0x2E);
                usleep(4600); // Should be not less than 4500
                $msb = i2c::readByte($I2CBUS, $i2c_address, 0xF6);
                $lsb = i2c::readByte($I2CBUS, $i2c_address, 0xF7);
                $ut = $msb << 8 | $lsb;
                // reading uncompensated pressure
                i2c::writeByte($I2CBUS, $i2c_address, 0xF4, 0x34 + ($oss << 6));
                usleep($sleep_time[$oss]);
                $msb_p = i2c::readByte($I2CBUS, $i2c_address, 0xF6);
                $lsb_p = i2c::readByte($I2CBUS, $i2c_address, 0xF7);
                $xlsb_p = i2c::readByte($I2CBUS, $i2c_address, 0xF8);
                $up = ($msb_p << 16 | $lsb_p << 8 | $xlsb_p) >> (8 - $oss);

                $x1 = (($ut - $ac6) * $ac5) / 32768;
                $x2 = ($mc * 2048) / ($x1 + $md);
                $b5 = $x1 + $x2;
                $b6 = $b5 - 4000;
                $x1 = ($b2 * (($b6 ^ 2) >> 12)) >> 11;
                $x2 = ($ac2 * $b6) >> 11;
                $x3 = $x1 + $x2;
                $b3 = ((($ac1 * 4 + $x3) << $oss) + 2) / 4;
                $x1 = ($ac3 * $b6) >> 13;
                $x2 = ($b1 * ($b6 ^ 2) >> 12) >> 16;
                $x3 = (($x1 + $x2) + 2) >> 2;
                $b4 = ($ac4 * ($x3 + 32768)) >> 15;
                $b7 = ($up - $b3) * (50000 >> $oss);
                if ($b7 < 0x80000000) {
                    $p = ($b7 * 2) / $b4;
                } else {
                    $p = ($b7 / $b4) * 2;
                }
                $x1 = ($p >> 8) * ($p >> 8);
                $x1 = ($x1 * 3038) >> 16;
                $x2 = (-7357 * $p) >> 16;
                $p = $p + (($x1 + $x2 + 3791) >> 4);
                $result = $p * 0.0075;
            } else {
                logger::writeLog('Неудачная попытка получить значение с I2C датчика с адресом:: ' . $i2c_address, loggerTypeMessage::ERROR);
            }
        } elseif ($model == 'LM75') {
            $ut = $ac1 = i2c::readUnShort($I2CBUS, $i2c_address, 0x00);
            $ut = $ut >> 5;
            $result = $ut * 0.125;
        } else {
            logger::writeLog('Неудачная попытка получить температуру с I2C датчика с адресом:: ' . $i2c_address, loggerTypeMessage::ERROR);
        }*/

        return $result;
    }

    private function getValueEthernet()
    {
        $result = null;
        $address = $this->getAddress();

        $json = file_get_contents($address);

        if ($json) {
            $data = json_decode($json);
            $result = $data->return_value / 100;
        } else {
            logger::writeLog('Неудачная попытка получить значение с Ethernet датчика с адресом:: ' . $address, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    public function getValue()
    {
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->getValueOWNet();
                    break;
                case netDevice::I2C :
                    $result = $this->getValueI2C();
                    break;
                case netDevice::ETHERNET_JSON :
                    $result = $this->getValueEthernet();
                    break;
            }
        }
        return $result;
    }

    abstract function requestData();

}

require_once dirname(__FILE__) . '/devices/temperature.device.class.php';
require_once dirname(__FILE__) . '/devices/humidity.device.class.php';
require_once dirname(__FILE__) . '/devices/pressure.device.class.php';
require_once dirname(__FILE__) . '/devices/keyIn.device.class.php';
require_once dirname(__FILE__) . '/devices/keyOut.device.class.php';

class labelSensorDevice extends aSensorDevice
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::LABEL);
    }

    function requestData()
    {
        // TODO: Implement requestData() method.
    }

}

class powerKeyMaker extends aMakerDevice
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::POWER_KEY);
    }

    private function getValueOWNet($channel = null)
    {
        $result = null;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $address = $this->getAddress();
        if (preg_match('/^3A\./', $address)) {
            $ow = new OWNet($OWNetAddress);
            $result = $ow->get('/uncached/' . $address . '/PIO.' . $channel);
            if (empty($result)) {
                $result = 0;
            }
            unset($ow);
        } else {
            logger::writeLog('Неудачная попытка получить значение с датчика :: ' . $address, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    private function setValueOWNet($value = null, $channel = null)
    {
        $result = null;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $address = $this->getAddress();
        if (preg_match('/^3A\./', $address)) {
            $ow = new OWNet($OWNetAddress);
            $result = $ow->set('/uncached/' . $address . '/PIO.' . $channel, $value);
            unset($ow);
        } else {
            logger::writeLog('Неудачная попытка записать значение в датчик :: ' . $address, loggerTypeMessage::ERROR, loggerName::ERROR);
        }

        return $result;
    }

    private function setValueMQTT($value = null, $status = statusKey::UNKNOWN, $timePause = '')
    {
        $result = true;
        try {
            $payload = $value . MQTT_CODE_SEPARATOR . $status . MQTT_CODE_SEPARATOR . $timePause;
            $mqtt = mqttSend::connect(true);
            $mqtt->publish($this->getTopicCmnd(), $payload);
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function getValue($channel = null)
    {
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->getValueOWNet($channel);
                    break;
            }
        }
        return $result;
    }

    public function setValue($value = null, $channel = null, $status = statusKey::UNKNOWN, $timePause = '')
    {
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->setValueOWNet($value, $channel);
                    break;
                case netDevice::ETHERNET_MQTT :
                    $result = $this->setValueMQTT($value, $status, $timePause);
                    break;
            }
        }
        return $result;
    }

    function getStatus()
    {
        // TODO: Implement getStatus() method.
    }

    function setData($data)
    {
        // TODO: Implement setData() method.
    }
}

