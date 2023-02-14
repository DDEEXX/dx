<?php
/** Датчик влажности
 */

class humiditySensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    const DEFAULT_PAYLOAD = 'humidity';

    public function __construct($mqttParameters)
    {
        if (empty($mqttParameters['payload'])) {
            $mqttParameters['payload'] = self::DEFAULT_PAYLOAD;
        }
        parent::__construct($mqttParameters, formatValueDevice::MQTT_HUMIDITY);
    }
}

class humiditySensorFactory
{
    static public function create($net, $mqttParameters)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new humiditySensorMQQTPhysic($mqttParameters);
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
        $mqttParameters = ['topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'payload' => $options['payload_cmnd']];
        $this->devicePhysic = humiditySensorFactory::create($this->getNet(), $mqttParameters);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


