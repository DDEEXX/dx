<?php

class formatterRadiatorValve implements iFormatterValue
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
        return $testCode;
    }

    function formatOutData($data)
    {
        $dataDecode = json_decode($data, true);
        if (is_null($dataDecode)) {
            return null;
        }
        $value = managerDevices::checkDataValue('value', $dataDecode);
        if (!is_null($value)) {
            if (strtolower($value) == 'on') {
                return '{"current_heating_setpoint":45}';
            } elseif (strtolower($value) == 'off') {
                return '{"current_heating_setpoint":5}';
            }
        }
        return null;
    }
}

class radiatorValve_MQTT extends aDeviceMakerPhysicMQTT
{
    public function __construct($parameters, $mqttParameters)
    {
        $this->selfState = true;
        $this->value = valuesFactory::createDeviceValue($parameters, new formatterRadiatorValve());
        parent::__construct($parameters['deviceID'], $mqttParameters);
    }

    function setData($data)
    {
        $payload = null;
        if ($this->value instanceof iDeviceValue) {
            $payload = $this->value->getFormatOutData($data);
        }
        if (is_null($payload)) return false;
        return parent::setData($payload);
    }

    public function formatTestPayload($testPayload, $ignoreUnknown = false)
    {
        if ($this->value instanceof iDeviceValue) {
            $testPayload = $this->value->getFormatTestCode($testPayload);
        }
        return parent::formatTestPayload($testPayload, $ignoreUnknown);
    }
}

class radiatorValve extends aMakerDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::RADIATOR_VALVE);
        $parameters = [
            'deviceID' => $this->getDeviceID(),
            'valueStorage'=>$options['value_storage']
        ] ;
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicSet' => $options['topic_cmnd'].'/set',
            'topicAvailability' => '',
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm']];
        $this->devicePhysic = new radiatorValve_MQTT($parameters, $mqttParameters);
    }
}