<?php

class formatterNewYearGarland_MQTT implements iFormatterValue
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
        return $data;
    }
}

class newYearGarland_MQTT extends aDeviceMakerPhysicMQTT
{
    public function __construct($parameters, $mqttParameters)
    {
        $this->selfState = true;
        $this->value = valuesFactory::createDeviceValue($parameters, new formatterNewYearGarland_MQTT());
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
            $testPayload = $this->value->getFormatTestCode($testPayload); //{"state":"online"}/{"state":"offline"}
        }
        return parent::formatTestPayload($testPayload, $ignoreUnknown);
    }
}


class newYearGarland extends aMakerDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::SWITCH_WHD02);
        $parameters = [
            'deviceID' => $this->getDeviceID(),
            'valueStorage'=>$options['value_storage']
        ] ;
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicSet' => $options['topic_cmnd'],
            'topicAvailability' => '',
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm']];
        $this->devicePhysic = new newYearGarland_MQTT($parameters, $mqttParameters);
    }
}