<?php
/** Датчик влажности
 */

class humiditySensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    public function __construct($topicCmnd, $topicStat, $topicTest)
    {
        parent::__construct($topicCmnd, $topicStat, $topicTest,'humidity', formatValueDevice::MQTT_HUMIDITY);
    }
}

class humiditySensorFactory
{
    static public function create($net, $topicCmnd, $topicStat, $topicTest)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new humiditySensorMQQTPhysic($topicCmnd, $topicStat, $topicTest);
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
        $topicTest = $options['topic_test'];
        $this->devicePhysic = humiditySensorFactory::create($this->getNet(), $topicCmnd, $topicStat, $topicTest);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


