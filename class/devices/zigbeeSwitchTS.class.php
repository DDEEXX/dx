<?php

class formatterSwitchTS implements iFormatterValue
{
    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;
        $arValueData = json_decode($value, true);
        switch ($arValueData['action']) {
            case '1_single' :
                $result->value = 1;
                break;
            case '2_single' :
                $result->value = 2;
                break;
            case '3_single' :
                $result->value = 3;
                break;
            case '1_double' :
                $result->value = 4;
                break;
            case '2_double' :
                $result->value = 5;
                break;
            case '3_double' :
                $result->value = 6;
                break;
            case '1_hold' :
                $result->value = 7;
                break;
            case '2_hold' :
                $result->value = 8;
                break;
            case '3_hold' :
                $result->value = 9;
                break;
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

class switchTS_MQTT extends aDeviceMakerPhysicMQTT
{
    public function __construct($parameters, $mqttParameters)
    {
        $this->selfState = true;
        $this->value = valuesFactory::createDeviceValue($parameters, new formatterSwitchTS());
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

class zigbeeSwitchTS extends aMakerDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::SWITCH_TUYA_TS);
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
        $this->devicePhysic = new switchTS_MQTT($parameters, $mqttParameters);
    }
}