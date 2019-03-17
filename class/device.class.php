<?php

require_once(dirname(__FILE__)."/globalConst.interface.php");
require_once(dirname(__FILE__)."/sqlDataBase.class.php");
require_once(dirname(__FILE__)."/logger.class.php");

if (file_exists("/opt/owfs/share/php/OWNet/ownet.php_"))
    require_once "/opt/owfs/share/php/OWNet/ownet.php";
elseif (file_exists("/usr/share/php/OWNet/ownet.php_"))
    require_once "/usr/share/php/OWNet/ownet.php";
elseif (file_exists(dirname(__FILE__).'/ownet.php'))
    require_once dirname(__FILE__).'/ownet.php';
else
    die("File 'ownet.php' is not found.");


/**
 * Interface iSensor
 */
interface iTemperatureSensor{
    public function getValue();
}

interface iDevice{
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
abstract class device implements iDevice {

    private $net = netDevice::NONE;
    private $adress = '';
    private $type = typeDevice::NONE;
    private $deviceID = 0;
    private $disabled = 0;
    protected $alarm    = '';

    public function __construct($deviceID, $net, $adr, $type, $disabled, $alarm = '') {
        $this->net = $net;
        $this->adress = $adr;
        $this->type = $type;
        $this->deviceID = $deviceID;
        $this->disabled = $disabled;
        $this->alarm = $alarm;
    }

    public function getDeviceID() { return $this->deviceID; }
    public function getNet() { return $this->net; }
    public function getAdress() { return $this->adress; }
    public function getType() { return $this->type; }
    public function getDisabled() {return $this->disabled;}
    public function getAlarm() { return ""; }

    public function addInBD()
    {
        $conn = sqlDataBase::Connect();
        $adr = $conn->getConnect()->real_escape_string($this->adress);
        $alarm = $conn->getConnect()->real_escape_string($this->alarm);

        $query = "INSERT tdevice (Adress, NetTypeID, SensorTypeID, Disabled, set_alarm) VALUES ('$adr', '$this->net', '$this->type', '$this->disabled', '$alarm')";

        return queryDataBase::execute($conn, $query);
    }

}

class sensor extends device {

    /**
     * sensor constructor.
     */
    public function __construct(array $options, $typeDevice)
    {
        $deviceID = $options['DeviceID'];
        $net = $options['NetTypeID'];
        $adress = $options['Adress'];
        $disabled = $options['Disabled'];
        $alarm = $options['set_alarm'];
        parent::__construct($deviceID, $net, $adress, $typeDevice, $disabled, $alarm);
    }

}

class maker extends device {

    /**
     * maker constructor.
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

class temperatureSensor extends sensor {

    public function __construct(array $options) {
        parent::__construct($options, typeDevice::TEMPERATURE);
    }

    private function getValueOWNet()
    {
        $result = null;
        $OWNetAdress = DB::getConst('OWNetAdress');
        $adress = $this->getAdress();
        if ( preg_match("/^28\./", $adress) ) { //это датчик DS18B20

            $ow = new OWNet($OWNetAdress);

            $tekValue = $ow->get('/uncached/'.$adress.'/temperature12');
            if ( is_null($tekValue) || $tekValue == "0") { //если датчик не сработал попробуем еще один раз
                sleep(1); //ждем 1 секунду
                $tekValue = $ow->get('/uncached/'.$adress.'/temperature12');
            }
            if ( !is_null($tekValue) ) { //если получили температуру, то возвращаем результат
                $result = $tekValue;
            }
            else { //запишем в лог об ошибке
                $log = logger::getLogger();
                $log->log('Ошибка получения температуры с датчика :: '.$adress, logger::ERROR);
                unset($log);
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
        else {
            $log = logger::getLogger();
            $log->log('Попытка получить температуру с датчика :: '.$adress, logger::ERROR);
            unset($log);
        }

        return $result;
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled==0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE : $result = $this->getValueOWNet(); break;
            }
        }
        return $result;
    }

}

class voltageSensor extends sensor  {

    public function __construct(array $options) {
        parent::__construct($options, typeDevice::VOLTAGE);
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

}

class labelSensor extends sensor  {

    public function __construct(array $options) {
        parent::__construct($options, typeDevice::LABEL);
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

}

class keyInSensor extends sensor  {

    public function __construct(array $options) {
        parent::__construct($options, typeDevice::KEY_IN);
        //т.к при подключении таких датчиков через 1wire состояние читается в "папке" alarm
        //поэтому при создании объекта если это датчик на 1wire запишем в физ устройство
        //значение срабатывания
//        if ($this->getNet() == netDevice::ONE_WIRE) {
//            $adress = $this->getAdress();
//            if ( preg_match("/^12\./", $adress) ) { //это датчик DS2406
//                $OWNetAdress = DB::getConst('OWNetAdress');
//                $ow = new OWNet($OWNetAdress);
//                $ow->set('/uncached/'.$adress.'/set_alarm', $this->getAlarm());
//                unset($ow);
//            }
//            else {
//                $log = logger::getLogger();
//                $log->log('Попытка установка alarm в датчик :: '.$adress, logger::ERROR);
//                unset($log);
//            }
//        }
    }

    private function getValueOWNet($chanel = null)
    {
        $result = 1; // 1 - это логическое "нет", из-за подключения датчика
        $address = $this->getAdress();
        $OWNetAdress = DB::getConst('OWNetAdress');
        if ( preg_match("/^12\./", $address) ) { //это датчик DS2406
            //такие датчики срабатывают по флагу set_alarm
            //когда сбывается условие записанное в этом флаге, в каталоге появляется файл с
            //именем равным адресу датчика

//            $owfsDir = DB::getConst('OWNetFS');
//            $alarmDir = $owfsDir.'/alarm/';
//
//            if (is_dir($alarmDir)) {
//                if ($dh = opendir($alarmDir)) {
//                    // ищем все сработавшие датчики на 1-wire
//                    while ( ($file = readdir($dh)) !== false ) {
//                        if ($file == $address) {
//                            $result = true;
//                            break;
//                        }
//                    }
//                }
//            }

            $ow = new OWNet($OWNetAdress);

            $tekValue = $ow->get('/uncached/'.$address.'/sensed.'.$chanel);
            if (is_null($tekValue)) {
                $tekValue = 1; //1 это нет
            }

            $result = $tekValue?0:1;

            unset($ow);

        }
        else {
            $log = logger::getLogger();
            $log->log('Попытка получить значение с датчика :: '.$address, logger::ERROR);
            unset($log);
        }

        return $result;
    }

    public function getValue($chanel = null)
    {
        // TODO: Implement getValue() method.
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled==0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE : $result = $this->getValueOWNet($chanel); break;
            }
        }
        return $result;
    }

    public function getAlarm() {
        return $this->alarm;
    }

}

class powerKeyMaker extends maker {

    public function __construct(array $options) {
        parent::__construct($options, typeDevice::POWER_KEY);
    }

    private function getValueOWNet($chanel = null)
    {
        $result = null;
        $OWNetAdress = DB::getConst('OWNetAdress');
        $adress = $this->getAdress();
        if ( preg_match("/^3A\./", $adress) ) {

            $ow = new OWNet($OWNetAdress);

            $result = $ow->get('/uncached/'.$adress.'/PIO.'.$chanel);

            if ( empty($result) ) {
                $result = 0;
            }

            unset($ow);

        }
        else {
            $log = logger::getLogger();
            $log->log('Попытка получить значение с датчика :: '.$adress, logger::ERROR);
            unset($log);
        }

        return $result;
    }

    private function setValueOWNet($value = null, $chanel = null)
    {
        $result = null;
        $OWNetAdress = DB::getConst('OWNetAdress');
        $adress = $this->getAdress();
        if ( preg_match("/^3A\./", $adress) ) {
            $ow = new OWNet($OWNetAdress);
            $ow->set('/uncached/'.$adress.'/PIO.'.$chanel, $value);
            unset($ow);
            $result = true;
        }
        else {
            $log = logger::getLogger();
            $log->log('Попытка записать значение в датчик :: '.$adress, logger::ERROR);
            unset($log);
        }

        return $result;
    }


    public function getValue($chanel = null)
    {
        // TODO: Implement getValue() method.
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled==0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE : $result = $this->getValueOWNet($chanel); break;
            }
        }
        return $result;
    }

    public function setValue($value = null, $chanel = null)
    {
        $result = null;
        $disabled = $this->getDisabled();
        if ($disabled==0) { // датчик включен
            switch ($this->getNet()) {
                case netDevice::ONE_WIRE : $result = $this->setValueOWNet($value, $chanel); break;
            }
        }
        return $result;
    }

    public function getAlarm() {
        return $this->alarm;
    }


}

class keyOutMaker extends maker {

    public function __construct(array $options) {
        parent::__construct($options, typeDevice::KEY_OUT);
    }

}
