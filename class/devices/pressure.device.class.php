<?php
/** Датчик атмосферного давления
 */

class pressureSensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    public function __construct($topic)
    {
        parent::__construct($topic, 'pressure', formatValueDevice::MQTT_PRESSURE);
    }
}

class pressureSensorFactory
{
    static public function create($net, $topic = null)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new pressureSensorMQQTPhysic($topic);
            default :
                return new DeviceSensorPhysicDefault();
        }
    }
}

class pressureSensorDevice extends aSensorDevice
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::PRESSURE);
        $this->devicePhysic = pressureSensorFactory::create($this->getNet(), $this->getTopicCmnd());
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


