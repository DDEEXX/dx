<?php
/** Датчик атмосферного давления
 */

class gasSensorMQQTPhysic extends aDeviceSensorPhysicMQTT

{
    const DEFAULT_PAYLOAD = '{"state": ""}';
    const DEFAULT_TEST_PAYLOAD = '{"availability": ""}';

    public function __construct($mqttParameters)
    {
        if (empty($mqttParameters['payload'])) $mqttParameters['payload'] = self::DEFAULT_PAYLOAD;
        if (empty($mqttParameters['testPayload'])) $mqttParameters['testPayload'] = self::DEFAULT_TEST_PAYLOAD;
        parent::__construct($mqttParameters, formatValueDevice::MQTT_GAS_SENSOR);
    }

    function formatTestPayload($testPayload, $ignoreUnknown = false)
    {
        $result = testDeviceCode::UNKNOWN;
        $test = json_decode($testPayload, true);
        if (array_key_exists('state', $test)) {
            $result = strtolower($test['state']) === 'online' ? testDeviceCode::WORKING : testDeviceCode::UNKNOWN;
        }
        return parent::formatTestPayload($result, $ignoreUnknown);
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

class gasSensorAlarmMQQT extends aAlarmMQTT
{

    public function saveInJournal($device, $payload)
    {
        $formatPayload = $this->convertPayload($payload);
        parent::saveInJournal($device, $formatPayload);
    }

    public function alarm($payload)
    {

    }

    function convertPayload($payload)
    {
        $data = json_decode($payload, true);
        $result['alarm'] = (bool)$data['alarm'];
        $result['value'] = (int)$data['gas'];
        return json_encode($result);
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
            'topicAvailability' => '',
            'topicSet' => $options['topic_cmnd'] . '/set',
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

    function onMessageAlarm($payload)
    {
        $this->alarm->saveInJournal($this, $payload);
    }
}