<?php

require_once(dirname(__FILE__)."/globalConst.interface.php");

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

    public function getValue()
    {
        // TODO: Implement getValue() method.
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
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

    public function getAlarm() { return $this->alarm; }

}

class powerKeyMaker extends maker {

    public function __construct(array $options) {
        parent::__construct($options, typeDevice::POWER_KEY);
    }

}

class keyOutMaker extends maker {

    public function __construct(array $options) {
        parent::__construct($options, typeDevice::KEY_OUT);
    }

}
