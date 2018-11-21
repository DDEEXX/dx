<?php

interface netDevice {
    const NONE          = 0;
    const ONE_WIRE      = 1;
    const ETHERNET      = 2;
    const CUBIEBOARD    = 3;
}

interface typeDevice{
    const NONE              = 0;
    const TEMPERATURE       = 1;
    const LABEL             = 2;
    const POWER_KEY         = 3;
    const KEY_IN            = 4;
    const KEY_OUT           = 5;
}

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

