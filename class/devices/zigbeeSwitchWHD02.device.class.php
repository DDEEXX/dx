<?php

class switchWHD02_MQTT extends aDeviceMakerPhysicMQTT
{

    public function __construct($mqttParameters)
    {
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

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function test()
    {
        $mqtt = mqttSend::connect();
        $topicCmnd = $this->getTopicTest().'/get';
        if (!empty($topicCmnd)) {
            $payload = '{"state": ""}';
            $mqtt->publish($topicCmnd, $payload);
        }
        unset($mqtt);
        return testDeviceCode::IS_MQTT_DEVICE;
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
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm']];
        $this->devicePhysic = new switchWHD02_MQTT($mqttParameters);
    }

}