<?php
/** Датчик атмосферного давления
 */

class formatterPressureMQTT_1 implements iFormatterValue
{
    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;
        $arValueData = json_decode($value, true);
        if (is_numeric($arValueData['pressure']))
            $result->value = $arValueData['pressure'];
        else {
            $result->value = '';
            $result->valueNull = true;
        }
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
        return $testCode;
    }

    function formatOutData($data)
    {
        return $data;
    }
}


class pressureSensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    private static function getConstructParam($parameters, &$mqttParameters)
    {
        $result = [];
        $result['payloadRequest'] = '';
        $result['selfState'] = false;
        $result['formatter'] = null;
        switch ($parameters['valueFormat']) {
            case 0 :
                $mqttParameters['payloadRequest'] = 'pressure';
                $result['selfState'] = false;
                $result['formatter'] = new formatterNumeric();
                break;
            case 1 :
                $mqttParameters['payloadRequest'] = '{"state": ""}';
                $mqttParameters['topicAvailability'] = '';
                $result['selfState'] = true;
                $result['formatter'] = new formatterPressureMQTT_1();
                break;
        }
        return $result;
    }

    public function __construct($parameters, $mqttParameters)
    {
        $param = self::getConstructParam($parameters, $mqttParameters);
        $this->selfState = $param['selfState'];
        $this->value = valuesFactory::createDeviceValue($parameters, $param['formatter']);
        parent::__construct($mqttParameters, formatValueDevice::MQTT_PRESSURE);
    }
}

class pressureSensorFactory
{
    static public function create($parameters, $mqttParameters)
    {
        switch ($parameters['net']) {
            case netDevice::ETHERNET_MQTT:
                return new pressureSensorMQQTPhysic($parameters, $mqttParameters);
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
        parent::__construct($options, typeDevice::HUMIDITY);
        $parameters = [
            'deviceID' => $this->getDeviceID(),
            'net' => $this->getNet(),
            'valueFormat' => $options['value_format'],
            'valueStorage' => $options['value_storage']
        ];
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm'],
            'payloadRequest' => $options['payload_cmnd']];
        $this->devicePhysic = pressureSensorFactory::create($parameters, $mqttParameters);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


