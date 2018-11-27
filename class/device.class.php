<?php

require_once(dirname(__FILE__)."/globalConst.interface.php");

/**
 * Interface iSensor
 */
interface iTemperatureSensor{
    public function getValue();
}

/**
 * Class device - абстрактный класс описывающий физическое устройство
 */
abstract class device {

    private $net = netDevice::NONE;
    private $adress = null;
    private $type = typeDevice::NONE;
    private $deviceID = 0;


    public function __construct($deviceID, $net, $adr, $type) {
        $this->net = $net;
        $this->adress = $adr;
        $this->type = $type;
        $this->deviceID = $deviceID;
    }

    /**
     * @return int
     */
    public function getDeviceID() { return $this->deviceID; }

    public function getNet() { return $this->net; }

    public function getAdress() { return $this->adress; }
    
    public function getType() { return $this->type; }

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
        parent::__construct($deviceID, $net, $adress, $typeDevice);
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
        parent::__construct($deviceID, $net, $adress, $typeDevice);
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
