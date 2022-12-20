<?php
/** Датчик влажности
 */

class humiditySensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    public function __construct($topicCmnd, $topicStat)
    {
        parent::__construct($topicCmnd, $topicStat, 'humidity', formatValueDevice::MQTT_HUMIDITY);
    }
}

class humiditySensorFactory
{
    static public function create($net, $topicCmnd, $topicStat)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new humiditySensorMQQTPhysic($topicCmnd, $topicStat);
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
        $topicCmnd = $options['topic_cmnd'];
        $topicStat = $options['topic_stat'];
        $this->devicePhysic = humiditySensorFactory::create($this->getNet(), $topicCmnd, $topicStat);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


