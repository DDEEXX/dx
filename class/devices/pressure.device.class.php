<?php
/** Датчик атмосферного давления
 */

class pressureSensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    const DEFAULT_PAYLOAD = 'pressure';

    public function __construct($mqttParameters)
    {
        if (empty($mqttParameters['payload'])) {
            $mqttParameters['payload'] = self::DEFAULT_PAYLOAD;
        }
        parent::__construct($mqttParameters, formatValueDevice::MQTT_PRESSURE);
    }
}

class pressureSensorFactory
{
    static public function create($net, $mqttParameters)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new pressureSensorMQQTPhysic($mqttParameters);
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
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm'],
            'payload' => $options['payload_cmnd']];
        $this->devicePhysic = pressureSensorFactory::create($this->getNet(), $mqttParameters);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


