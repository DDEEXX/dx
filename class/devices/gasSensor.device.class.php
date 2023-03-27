<?php
/** Датчик атмосферного давления
 */

class gasSensorMQQTPhysic extends aDeviceSensorPhysicMQTT

{
    const DEFAULT_PAYLOAD = 'get';

    public function __construct($mqttParameters)
    {
        if (empty($mqttParameters['payload'])) {
            $mqttParameters['payload'] = self::DEFAULT_PAYLOAD;
        }
        parent::__construct($mqttParameters, formatValueDevice::MQTT_GAS_SENSOR);
    }
}

class gasSensorFactory
{
    static public function create($net, $mqttParameters)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new gasSensorMQQTPhysic($mqttParameters);
            default :
                return new DeviceSensorPhysicDefault();
        }
    }
}

class gasSensor extends aSensorDevice
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::GAS_SENSOR);
        $mqttParameters = ['topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'payload' => $options['payload_cmnd']];
        $this->devicePhysic = gasSensorFactory::create($this->getNet(), $mqttParameters);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}