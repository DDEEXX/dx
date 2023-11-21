<?php

class formatterSwitchTS implements iFormatterValue
{
    function formatRawValue($value)
    {
        $arValueData = json_decode($value, true);
        if (array_key_exists('action', $arValueData)) return $arValueData['action'];
        return null;
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
            'valueStorage'=>$options['value_storage'],
            'options'=>$options['options']
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