<?php
/** Датчик газа
 */

class formatterGasSensorMQTT extends aFormatterValue
{
    function formatRawValue($value)
    {
        $result = new stdClass();
        $result->value = json_decode($value);
        return $result;
    }

    function formatTestCode($value)
    {
        $objValue = json_decode($value);
        switch ($objValue->state) {
            case 'online' :
                $testCode = testDeviceCode::WORKING;
                break;
            case 'offline' :
                $testCode = testDeviceCode::NO_CONNECTION;
                break;
            default :
                $testCode = testDeviceCode::UNKNOWN;
        }
        return parent::formatTestCode($testCode);
    }
}

class gasSensorMQQTPhysic extends aDeviceSensorPhysicMQTT

{
    private static function getConstructParam($parameters, &$mqttParameters)
    {
        $result = [];
        $result['selfState'] = true;
        $result['formatter'] = new formatterGasSensorMQTT();
        $mqttParameters['payloadRequest'] = '{"state": ""}';
        $mqttParameters['topicAvailability'] = '';
        return $result;
    }

    public function __construct($parameters, $mqttParameters)
    {
        $param = self::getConstructParam($parameters, $mqttParameters);
        $this->selfState = $param['selfState'];
        $this->value = valuesFactory::createDeviceValue($parameters, $param['formatter']);
        parent::__construct($parameters['deviceID'], $mqttParameters, formatValueDevice::MQTT_GAS_SENSOR);
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
    static public function create($parameters, $mqttParameters)
    {
        switch ($parameters['net']) {
            case netDevice::ETHERNET_MQTT:
                return new gasSensorMQQTPhysic($parameters, $mqttParameters);
            default :
                return new DeviceSensorPhysicDefault($parameters['deviceID']);
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
        $parameters = [
            'deviceID' => $this->getDeviceID(),
            'net' => $this->getNet(),
            'valueFormat' => $options['value_format'],
            'valueStorage' => $options['value_storage']
        ];
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicSet' => $options['topic_cmnd'] . '/set',
            'topicTest' => $options['topic_test']];
        $this->devicePhysic = gasSensorFactory::create($parameters, $mqttParameters);
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