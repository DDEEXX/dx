<?php

require_once(dirname(__FILE__) . '/../../class/mqtt.class.php');
require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

$property = $_POST['property'];
$value = $_POST['value'];
if (!is_numeric($value)) {
    exit;
}
$unit = managerUnits::getUnitLabel('kitchen_hood');
if (is_null($unit)) exit;

$device = $unit->getDevice();
if (is_null($device)) exit;

$devicePhysic = $device->getDevicePhysic();
if (is_null($devicePhysic)) exit;

$topic = $devicePhysic->getTopicSet();

$value = (int)$value;
$payload = json_encode([$property => $value]);

$mqtt = mqttSend::connect();
$mqtt->publish($topic, $payload);

