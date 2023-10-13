<?php
/** Датчик влажности
 */
class formatterHumidityMQTT_1 implements iFormatterValue
{
    function formatRawValue(array $value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;
        $arValueData = json_decode($value['value'], true);
        if ($arValueData['enable_sensor'] && is_numeric($arValueData['humidity']))
            $result->value = $arValueData['temperature'];
        else {
            $result->value = '';
            $result->valueNull = true;
        }
        return $result;
    }

    function formatTestCode($value)
    {
        $arValue = json_decode($value);
        switch ($arValue->state) {
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
}

class humiditySensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    private static function getConstructParam($parameters)
    {
        $result = [];
        $result['payloadRequest'] = '';
        $result['selfActivity'] = false;
        $result['formatter'] = null;
        switch ($parameters['valueFormat']) {
            case 0 :
                $result['payloadRequest'] = 'humidity';
                $result['selfActivity'] = false;
                $result['formatter'] = new formatterNumeric();
                break;
            case 1 :
                $result['payloadRequest'] = '{"state": ""}';
                $result['selfActivity'] = true;
                $result['formatter'] = new formatterHumidityMQTT_1();
                break;
        }
        return $result;
    }

    public function __construct($parameters, $mqttParameters)
    {
        $param = self::getConstructParam($parameters);
        $mqttParameters['payloadRequest'] = $param['payloadRequest'];
        $this->selfActivity = $param['selfActivity'];
        $this->value = valuesFactory::createDeviceValue($parameters, $param['formatter']);
        parent::__construct($mqttParameters, formatValueDevice::MQTT_HUMIDITY);
    }
}

class humiditySensorFactory
{
    static public function create($parameters, $mqttParameters)
    {
        switch ($parameters['net']) {
            case netDevice::ETHERNET_MQTT:
                return new humiditySensorMQQTPhysic($parameters, $mqttParameters);
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
        $this->devicePhysic = humiditySensorFactory::create($parameters, $mqttParameters);
    }

    function requestData($ignoreActivity = true)
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}


