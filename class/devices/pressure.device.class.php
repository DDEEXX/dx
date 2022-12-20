<?php
/** Датчик атмосферного давления
 */

class pressureSensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    public function __construct($topicCmnd, $topicStat)
    {
        parent::__construct($topicCmnd, $topicStat, 'pressure', formatValueDevice::MQTT_PRESSURE);
    }
}

class pressureSensorFactory
{
    static public function create($net, $topicCmnd, $topicStat)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new pressureSensorMQQTPhysic($topicCmnd, $topicStat);
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
        $topicCmnd = $options['topic_cmnd'];
        $topicStat = $options['topic_stat'];
        $this->devicePhysic = pressureSensorFactory::create($this->getNet(), $topicCmnd, $topicStat);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


