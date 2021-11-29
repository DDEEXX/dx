<?php

require_once(dirname(__FILE__) . "/globalConst.interface.php");
require_once(dirname(__FILE__) . "/sqlDataBase.class.php");
require_once(dirname(__FILE__) . "/i2c.class.php");
require_once(dirname(__FILE__) . "/logger.class.php");
require_once(dirname(__FILE__) . "/sharedMemory.class.php");
require_once(dirname(__FILE__) . '/mqtt.class.php');

//if (file_exists("/opt/owfs/share/php/OWNet/ownet.php_"))
//    /** @noinspection PhpIncludeInspection */
//    require_once "/opt/owfs/share/php/OWNet/ownet.php";
//elseif (file_exists("/usr/share/php/OWNet/ownet.php_"))
//    require_once "/usr/share/php/OWNet/ownet.php";
//elseif (file_exists(dirname(__FILE__) . '/ownet.php'))
require_once dirname(__FILE__) . '/ownet.php';
//else
//    die("File 'ownet.php' is not found.");


/**
 * Interface iSensor
 */
interface iTemperatureSensor
{
    public function getValue();

}

interface iDevice
{
    public function getDeviceID();
    public function getNet();
    public function getAddress();
    public function getType();
    public function getDisabled();
    public function getAlarm();
    public function addInBD();
    public function test();

}

/**
 * Class device - абстрактный класс описывающий физическое устройство
 */
abstract class device implements iDevice
{

    private $net;
    private $address;
    private $type;
    private $deviceID;
    private $disabled;
    protected $alarm = null;
    protected $model = null;
    protected $topicCmnd = null;
    protected $topicStat = null;
    protected $topicTest = null;

    public function __construct($deviceID, $net, $adr, $type, $disabled, $alarm = null, $model = null,
                                $topicCmnd = null, $topicStat = null, $topicTest = null)
    {
        $this->net = $net;
        $this->address = $adr;
        $this->type = $type;
        $this->deviceID = $deviceID;
        $this->disabled = $disabled;
        if(empty($alarm{0})) { $this->alarm = null; }
        else { $this->alarm = $alarm; }
        $this->model = $model;
        $this->topicCmnd = $topicCmnd;
        $this->topicStat = $topicStat;
        $this->topicTest = $topicTest;
    }

    /**
     * @return null
     */
    public function getModel()
    {
        return $this->model;
    }

    public function getDeviceID()
    {
        return $this->deviceID;
    }

    public function getNet()
    {
        return $this->net;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDisabled()
    {
        return $this->disabled;
    }

    public function getAlarm()
    {
        return $this->alarm;
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

    public function test() {
        return testUnitCode::WORKING;
    }

    /**
     * Получить подписку MQTT для отправки
     * @return string|null
     */
    public function getTopicCmnd()
    {
        if (is_string($this->topicCmnd)) {
            return trim($this->topicCmnd);
        }
        else
            return null;
    }

    /**
     * Получить подписку статуса MQTT
     * @return string|null
     */
    public function getTopicStat()
    {
        if (is_string($this->topicStat)) {
            return trim($this->topicStat);
        }
        else
            return null;
    }

    /**
     * @return mixed|null
     */
    public function getTopicTest()
    {
        if (is_string($this->topicTest)) {
            return $this->topicTest;
        }
        else
            return null;
    }

}

class maker extends device
{

    /**
     * maker constructor.
     * @param array $options
     * @param $typeDevice
     */
    public function __construct(array $options, $typeDevice)
    {
        $deviceID = $options['DeviceID'];
        $net = $options['NetTypeID'];
        $address = $options['Address'];
        $disabled = $options['Disabled'];
        $topicCmnd = $options['topic_cmnd'];
        $topicStat = $options['topic_stat'];
        $topicTest = $options['topic_test'];
        parent::__construct($deviceID, $net, $address, $typeDevice, $disabled,null,null,$topicCmnd,$topicStat,$topicTest);
    }

}

class sensor extends device
{

    /**
     * sensor constructor.
     * @param array $options
     * @param $typeDevice
     */
    public function __construct(array $options, $typeDevice)
    {
        $deviceID = $options['DeviceID'];
        $net = $options['NetTypeID'];
        $address = $options['Address'];
        $disabled = $options['Disabled'];
        $alarm = $options['set_alarm'];
        $model = $options['model'];
        $topicCmnd = $options['topic_cmnd'];
        $topicStat = $options['topic_stat'];
        $topicTest = $options['topic_test'];

        parent::__construct($deviceID, $net, $address, $typeDevice, $disabled, $alarm, $model, $topicCmnd, $topicStat, $topicTest);
        //parent::__construct($options, $typeDevice);
    }

    private function getValueOWNet()
    {
        $result = null;
        //$OWNetAdress = DB::getConst('OWNetAddress');
        //$OWNetDir = DB::getConst('OWNETDir');

        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);

        $address = $this->getAddress();
        if (preg_match("/^28\./", $address)) { //это датчик DS18B20

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

            $f = file($OWNetDir .'/'. $address . "/temperature12");
            if ($f === false) { //попробуем еще раз
                usleep(500000); //ждем 0.5 секунд
                $f = file($OWNetDir .'/'. $address . "/temperature12");
            }

            if ($f === false) {
                logger::writeLog('Ошибка получения температуры с датчика :: ' . $address, loggerTypeMessage::ERROR);
                $result = null;
            }
            else {
                $result = $f[0];
            }

        }
/*        elseif (preg_match("/^12\./", $address)) { //это датчик DS2406

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
        $I2CBUS = DB::getConst('I2CBUS');
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
            }
            elseif ($this->getType() == typeDevice::PRESSURE) {
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
                }
                else {
                    $p = ($b7 / $b4) * 2;
                }
                $x1 = ($p >> 8) * ($p >> 8);
                $x1 = ($x1 * 3038) >> 16;
                $x2 = (-7357 * $p) >> 16;
                $p = $p + (($x1 + $x2 + 3791) >> 4);
                $result = $p * 0.0075;
            }
            else {
                logger::writeLog('Неудачная попытка получить значение с I2C датчика с адресом:: ' . $i2c_address, loggerTypeMessage::ERROR);
            }
        }
        elseif ($model == 'LM75') {
            $ut = $ac1 = i2c::readUnShort($I2CBUS, $i2c_address, 0x00);
            $ut = $ut >> 5;
            $result = $ut * 0.125;
        }
        else {
            logger::writeLog('Неудачная попытка получить температуру с I2C датчика с адресом:: ' . $i2c_address, loggerTypeMessage::ERROR);
        }

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
        }
        else {
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

}

class humiditySensor extends sensor
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::HUMIDITY);
    }

}

class temperatureSensor extends sensor implements iTemperatureSensor
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::TEMPERATURE);
    }

    /**
     * Получить значение температуры непосредственно с датчика
     * @return float|int|mixed|null
     */
    public function getValue()
    {
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->getValueOWNet();
                    break;
            }
        }
        return $result;
    }

    private function getValueOWNet()
    {
        $result = null;

        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);

        $address = $this->getAddress();
        if (preg_match("/^28\./", $address)) { //это датчик DS18B20

            $f = file($OWNetDir .'/'. $address . "/temperature12");
            if ($f === false) { //попробуем еще раз
                usleep(500000); //ждем 0.5 секунд
                $f = file($OWNetDir .'/'. $address . "/temperature12");
            }

            if ($f === false) {
                logger::writeLog('Ошибка получения температуры с датчика :: ' . $address, loggerTypeMessage::ERROR);
                $result = null;
            }
            else {
                $result = $f[0];
            }

        }
        else {
            logger::writeLog('Неудачная попытка получить данные с датчика :: ' . $address, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    private function testOWNet()
    {
        $result = testUnitCode::NO_CONNECTION;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $address = $this->getAddress();
        if (preg_match("/^28\./", $address)) { //это датчик DS18B20
            /** @noinspection PhpUndefinedClassInspection */
            $ow = new OWNet($OWNetAddress);
            for ($i=0; $i<5; $i++ ) {
                $temperature = $ow->get($address.'/temperature12');
                if (!is_null($temperature)) {
                    $result = testUnitCode::WORKING;
                    break;
                }
            }
        }
        else {
            $result = testUnitCode::ONE_WIRE_ADDRESS;
        }

        return $result;

    }

    public function test()
    {
        $result = testUnitCode::WORKING;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->testOWNet();
                    break;
            }
        }
        else {
            $result = testUnitCode::DISABLED;
        }
        return $result;
    }

}

class pressureSensor extends sensor
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::PRESSURE);
    }

}

class keyInSensor extends sensor
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KEY_IN);
    }

    /**
     *  Устанавливает set_alarm у физического датчика в соответствии со свойством alarm
     */
    public function updateAlarm() {
        $result = false;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $address = $this->getAddress();
        if (preg_match("/^12\./", $address)) {
            /** @noinspection PhpUndefinedClassInspection */
            $ow = new OWNet($OWNetAddress);
            $result = $ow->set('/' . $address . '/set_alarm', $this->getAlarm());
            unset($ow);
        }
        else {
            logger::writeLog('что-то странное с датчиком :: ' . $address, loggerTypeMessage::ERROR, loggerName::ERROR);
        }

        if (!$result) {
            logger::writeLog('Ошибка установки set_alarm у датчика :: '.$address, loggerTypeMessage::ERROR, loggerName::ERROR);
        }

        return $result;
    }
}

class voltageSensor extends sensor
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::VOLTAGE);
    }

}

class labelSensor extends sensor
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::LABEL);
    }

}

class powerKeyMaker extends maker
{
    const TEST_CODE = 'test';

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::POWER_KEY);
    }

    private function getValueOWNet($channel = null)
    {
        $result = null;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $address = $this->getAddress();
        if (preg_match("/^3A\./", $address)) {

            /** @noinspection PhpUndefinedClassInspection */
            $ow = new OWNet($OWNetAddress);

            $result = $ow->get('/uncached/' . $address . '/PIO.' . $channel);

            if (empty($result)) {
                $result = 0;
            }

            unset($ow);

        }
        else {
            logger::writeLog('Неудачная попытка получить значение с датчика :: ' . $address, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    private function setValueOWNet($value = null, $channel = null)
    {
        $result = null;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $address = $this->getAddress();
        if (preg_match("/^3A\./", $address)) {
            /** @noinspection PhpUndefinedClassInspection */
            $ow = new OWNet($OWNetAddress);
            $result = $ow->set('/uncached/' . $address . '/PIO.' . $channel, $value);
            unset($ow);
        }
        else {
            logger::writeLog('Неудачная попытка записать значение в датчик :: ' . $address, loggerTypeMessage::ERROR, loggerName::ERROR);
        }

        return $result;
    }

    private function setValueMQTT($value = null, $status = statusKey::UNKNOWN) {
        $result = true;
        try {
            $payload = $value.MQTT_CODE_SEPARATOR.$status;
            $mqtt = mqttSend::connect(true);
            $mqtt->publish($this->getTopicCmnd(), $payload);
        }
        catch (Exception $e) {
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

    public function setValue($value = null, $channel = null, $status = statusKey::UNKNOWN)
    {
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->setValueOWNet($value, $channel);
                    break;
                case netDevice::ETHERNET_MQTT :
                    $result = $this->setValueMQTT($value, $status);
                    break;
            }
        }
        return $result;
    }

    public function test()
    {
        $result = testUnitCode::WORKING;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->testOWNet();
                    break;
                case netDevice::ETHERNET_MQTT :
                    $result = $this->testMQTT();
                    break;
            }
        }
        else {
            $result = testUnitCode::DISABLED;
        }
        return $result;
    }

    private function testOWNet() {
        return testUnitCode::WORKING;
    }

    private function testMQTT() {
        $mqtt = mqttTest::Connect();
        $mqtt->publish($this->getTopicTest(), self::TEST_CODE);
    }

}

class keyOutMaker extends maker
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KEY_OUT);
    }

}
