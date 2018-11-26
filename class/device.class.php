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

    public function __construct($net, $adr, $type) {
        $this->net = $net;
        $this->adress = $adr;
        $this->type = $type;
    }

    public function getNet() { return $this->net; }

    public function getAdress() { return $this->adress; }
    
    public function getType() { return $this->type; }

}

class sensor extends device {

}

class temperatureSensor extends sensor implements iTemperatureSensor  {

    public function __construct($net, $adr) {
        parent::__construct($net, $adr, typeDevice::TEMPERATURE);
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

}

