<?php

class switchWHD02_MQTT extends aDeviceMakerPhysicMQTT
{

    const DEFAULT_TEST_PAYLOAD = '{"state":"online"}';

    public function __construct($mqttParameters)
    {
        if (empty($mqttParameters['testPayload'])) $mqttParameters['testPayload'] = self::DEFAULT_TEST_PAYLOAD;
        parent::__construct($mqttParameters, formatValueDevice::MQTT_SWITCH_WHD02);
    }

    function setData($data)
    {
        $dataDecode = json_decode($data, true);
        if (is_null($dataDecode)) {
            return false;
        }
        $value = managerDevices::checkDataValue('value', $dataDecode);
        if (!is_null($value)) {
            if (strtolower($value) == 'on') {
                $payload = '{"state": "ON"}';
            } elseif (strtolower($value) == 'off') {
                $payload = '{"state": "OFF"}';
            }
            else {
                return false;
            }
            return parent::setData($payload);
        } else {
            return false;
        }

    }

    function formatTestPayload($testPayload)
    {
        $result = testDeviceCode::UNKNOWN;
        $test = json_decode($testPayload, true);
        if (array_key_exists('state', $test)) {
            $result = strtolower($test['state']) === 'online' ? testDeviceCode::WORKING : testDeviceCode::UNKNOWN;
        }
        return parent::formatTestPayload($result);
    }

}

class zigbeeSwitchWHD02 extends aMakerDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::SWITCH_WHD02);
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicAvailability' => $options['topic_test'],
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm']];
        $this->devicePhysic = new switchWHD02_MQTT($mqttParameters);
    }

}