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

class gasSensorAlarmMQQT extends aAlarmMQTT {

    public function alarm($payload)
    {
        $data = json_decode($payload, true);
        $alarm = (bool)$data['alarm'];
        $value = $data['gas'];

    }
}

class gasSensor extends aSensorDevice implements iDeviceAlarm
{
    private $alarm; // объект отвечающий за события тревоги поступившие с датчика

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::GAS_SENSOR);
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'payload' => $options['payload_cmnd']];
        $this->devicePhysic = gasSensorFactory::create($this->getNet(), $mqttParameters);
        $this->alarm = managerAlarmDevice::createAlarm($options['topic_alarm'], $this->devicePhysic);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }

    function getTopicAlarm()
    {
        return $this->alarm->getTopicAlarm();
    }

    function alarm($payload)
    {
        $this->alarm->alarm($payload);
    }
}