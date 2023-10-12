<?php

class kitchenHood_MQTT extends aDeviceSensorPhysicMQTT
{
    public function __construct($mqttParameters)
    {
        parent::__construct($mqttParameters, formatValueDevice::MQTT_KITCHEN_HOOD);
    }
}

class kitchenHood extends aSensorDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KITCHEN_HOOD);
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm'],
            'payloadRequest' => $options['payload_cmnd']];
        $this->devicePhysic = new kitchenHood_MQTT($mqttParameters);
    }

    function requestData($ignoreActivity = true)
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}