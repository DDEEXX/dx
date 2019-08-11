<?php

require_once(dirname(__FILE__) . "/globalConst.interface.php");
require_once(dirname(__FILE__) . "/sqlDataBase.class.php");
require_once(dirname(__FILE__) . "/i2c.class.php");
require_once(dirname(__FILE__) . "/logger.class.php");

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

    public function getModel();
}

interface iDevice
{
    public function getDeviceID();

    public function getNet();

    public function getAdress();

    public function getType();

    public function getDisabled();

    public function getAlarm();

    public function addInBD();
}

/**
 * Class device - абстрактный класс описывающий физическое устройство
 */
abstract class device implements iDevice
{

    private $net = netDevice::NONE;
    private $adress = '';
    private $type = typeDevice::NONE;
    private $deviceID = 0;
    private $disabled = 0;
    protected $alarm = '';
    protected $model = null;

    public function __construct($deviceID, $net, $adr, $type, $disabled, $alarm = '', $model = null)
    {
        $this->net = $net;
        $this->adress = $adr;
        $this->type = $type;
        $this->deviceID = $deviceID;
        $this->disabled = $disabled;
        $this->alarm = $alarm;
        $this->model = $model;
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

    public function getAdress()
    {
        return $this->adress;
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
        return "";
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
        $adr = $conn->getConnect()->real_escape_string($this->adress);
        $alarm = $conn->getConnect()->real_escape_string($this->alarm);

        $query = "INSERT tdevice (Adress, NetTypeID, SensorTypeID, Disabled, set_alarm) VALUES ('$adr', '$this->net', '$this->type', '$this->disabled', '$alarm')";

        try {
            return queryDataBase::execute($conn, $query);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при выполнении sql запроса (device.class.php) функция addInBD.' . $e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
            return false;
        }
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
        $adress = $options['Adress'];
        $disabled = $options['Disabled'];
        $alarm = $options['set_alarm'];
        $model = $options['model'];
        parent::__construct($deviceID, $net, $adress, $typeDevice, $disabled, $alarm, $model);
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
        $adress = $options['Adress'];
        $disabled = $options['Disabled'];
        parent::__construct($deviceID, $net, $adress, $typeDevice, $disabled);
    }
}

class sensors extends sensor
{

    public function __construct(array $options, $typeDevice)
    {
        parent::__construct($options, $typeDevice);
    }

    private function getValueOWNet($chanel = null)
    {
        $result = null;
        $OWNetAdress = DB::getConst('OWNetAdress');
        $adress = $this->getAdress();
        if (preg_match("/^28\./", $adress)) { //это датчик DS18B20

            /** @noinspection PhpUndefinedClassInspection */
            $ow = new OWNet($OWNetAdress);

            $tekValue = $ow->get('/uncached/' . $adress . '/temperature12');
            if (is_null($tekValue) || $tekValue == "0") { //если датчик не сработал попробуем еще один раз
                sleep(1); //ждем 1 секунду
                $tekValue = $ow->get('/uncached/' . $adress . '/temperature12');
            }
            if (!is_null($tekValue)) { //если получили температуру, то возвращаем результат
                $result = $tekValue;
            }
            else { //запишем в лог об ошибке
                logger::writeLog('Ошибка получения температуры с датчика :: ' . $adress, loggerTypeMessage::ERROR);
            }

            if (!is_null($tekValue)) { //иногда кодга датчик не срабатывает, возвращает 0
                if ($tekValue == 0) {
                    //т.е. 0 датчик никогда не вернет, но это очень редкая ситуация
                    //поэтому лучше без 0, чем провалы (т.е 15.0, 15.1, 15.2, 0 , 15.2, 15,3)
                    $result = null;
                }
            }

            unset($ow);

        }
        elseif (preg_match("/^12\./", $adress)) { //это датчик DS2406

            /** @noinspection PhpUndefinedClassInspection */
            $ow = new OWNet($OWNetAdress);

            $tekValue = $ow->get('/uncached/' . $adress . '/sensed.' . $chanel);
            if (is_null($tekValue)) {
                $tekValue = 1; //1 это нет
            }

            $result = $tekValue ? 0 : 1;

            unset($ow);

        }
        else {
            logger::writeLog('Неудачаная попытка получить данные с датчика :: ' . $adress, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    private function getValueI2C()
    {
        $result = null;
        $I2CBUS = DB::getConst('I2CBUS');
        $i2c_address = $this->getAdress();
        $model = $this->getModel();
        if ($model == 'BMP180') { //это датчик DS18B20
            if ($this->getType() == typeDevice::TEMPERATURE) {


                $ac5 = i2c::readUnShort($I2CBUS, $i2c_address, 0xB2);
                $ac6 = i2c::readUnShort($I2CBUS, $i2c_address, 0xB4);
                $mc = i2c::readShort($I2CBUS, $i2c_address, 0xBC);
                $md = i2c::readShort($I2CBUS, $i2c_address, 0xBE);

                // reading uncompensated temperature
                i2c::writeByte($I2CBUS, $i2c_address, 0xF4, 0x2E);
                usleep(4600); // Should be not less then 4500
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
                usleep(4600); // Should be not less then 4500
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
                logger::writeLog('Неудачаная попытка получить значение с I2C датчика с адресом:: ' . $i2c_address, loggerTypeMessage::ERROR);
            }
        }
        elseif ($model == 'LM75') {
            $ut = $ac1 = i2c::readUnShort($I2CBUS, $i2c_address, 0x00);
            $ut = $ut >> 5;
            $result = $ut * 0.125;
        }
        else {
            logger::writeLog('Неудачаная попытка получить температуру с I2C датчика с адресом:: ' . $i2c_address, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    private function getValueEthernet()
    {
        $result = null;
        $address = $this->getAdress();

        $json = file_get_contents($address);

        if ($json) {
            $data = json_decode($json);
            $result = $data->return_value / 100;
        }
        else {
            logger::writeLog('Неудачаная попытка получить значение с Ethernet датчика с адресом:: ' . $address, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    public function getValue($chanel = null)
    {
        // TODO: Implement getValue() method.
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->getValueOWNet($chanel);
                    break;
                case netDevice::I2C :
                    $result = $this->getValueI2C();
                    break;
                case netDevice::ETHERNET :
                    $result = $this->getValueEthernet();
                    break;
            }
        }
        return $result;
    }

}

class humiditySensor extends sensors
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::HUMIDITY);
    }

}

class temperatureSensor extends sensors
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::TEMPERATURE);
    }

}

class pressureSensor extends sensors
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::PRESSURE);
    }

}

class keyInSensor extends sensors
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KEY_IN);
    }

    public function getAlarm()
    {
        return $this->alarm;
    }

}

class voltageSensor extends sensors
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::VOLTAGE);
    }

}

class labelSensor extends sensors
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::LABEL);
    }

}

class powerKeyMaker extends maker
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::POWER_KEY);
    }

    private function getValueOWNet($chanel = null)
    {
        $result = null;
        $OWNetAdress = DB::getConst('OWNetAdress');
        $adress = $this->getAdress();
        if (preg_match("/^3A\./", $adress)) {

            /** @noinspection PhpUndefinedClassInspection */
            $ow = new OWNet($OWNetAdress);

            $result = $ow->get('/uncached/' . $adress . '/PIO.' . $chanel);

            if (empty($result)) {
                $result = 0;
            }

            unset($ow);

        }
        else {
            logger::writeLog('Неудачаная попытка получить значение с датчика :: ' . $adress, loggerTypeMessage::ERROR);
        }

        return $result;
    }

    private function setValueOWNet($value = null, $chanel = null)
    {
        $result = null;
        $OWNetAdress = DB::getConst('OWNetAdress');
        $adress = $this->getAdress();
        if (preg_match("/^3A\./", $adress)) {
            /** @noinspection PhpUndefinedClassInspection */
            $ow = new OWNet($OWNetAdress);
            $result = $ow->set('/uncached/' . $adress . '/PIO.' . $chanel, $value);
            unset($ow);
        }
        else {
            logger::writeLog('Неудачаная попытка записать значение в датчик :: ' . $adress, loggerTypeMessage::ERROR);
        }

        return $result;
    }


    public function getValue($chanel = null)
    {
        // TODO: Implement getValue() method.
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->getValueOWNet($chanel);
                    break;
            }
        }
        return $result;
    }

    public function setValue($value = null, $chanel = null)
    {
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled == 0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE :
                    $result = $this->setValueOWNet($value, $chanel);
                    break;
            }
        }
        return $result;
    }

    public function getAlarm()
    {
        return $this->alarm;
    }


}

class keyOutMaker extends maker
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KEY_OUT);
    }

}
