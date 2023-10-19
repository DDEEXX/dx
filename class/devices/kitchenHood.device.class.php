<?php

class formatterKitchenHood implements iFormatterValue
{
    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;
        $arValueData = json_decode($value, true);
        if (!is_null($arValueData)) $result->value = $arValueData['state'] == 'ON' ? 1 : 0;
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
                return '{"state": "ON"}';
            } elseif (strtolower($value) == 'off') {
                return '{"state": "OFF"}';
            }
        }
        return null;
    }
}

class kitchenHood_MQTT extends aDeviceSensorPhysicMQTT
{
    public function __construct($parameters, $mqttParameters)
    {
        $this->selfState = true;
        $this->value = valuesFactory::createDeviceValue($parameters, new formatterKitchenHood());
        parent::__construct($mqttParameters, formatValueDevice::MQTT_KITCHEN_HOOD);
    }

    public function formatTestPayload($testPayload, $ignoreUnknown = false)
    {
        if ($this->value instanceof iDeviceValue) {
            $testPayload = $this->value->getFormatTestCode($testPayload);
        }
        return parent::formatTestPayload($testPayload, $ignoreUnknown);
    }

}

class kitchenHood extends aSensorDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KITCHEN_HOOD);
        $parameters = [
            'deviceID' => $this->getDeviceID(),
            'valueStorage'=>1 //всегда в базе данных
        ] ;
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'topicAvailability' => '',
            'topicAlarm' => $options['topic_alarm']];
        $this->devicePhysic = new kitchenHood_MQTT($parameters, $mqttParameters);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}