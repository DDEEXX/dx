<?php
/** Датчик влажности
 */

class humiditySensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    public function __construct($topic)
    {
        parent::__construct($topic, 'humidity', formatValueDevice::MQTT_HUMIDITY);
    }
}

class humiditySensorFactory
{
    static public function create($net, $topic = null)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new humiditySensorMQQTPhysic($topic);
            default :
                return new DeviceSensorPhysicDefault();
        }
    }
}

class humiditySensorDevice extends aSensorDevice
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::HUMIDITY);
        $this->devicePhysic = humiditySensorFactory::create($this->getNet(), $this->getTopicCmnd());
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


