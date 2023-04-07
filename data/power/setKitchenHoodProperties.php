<?php

require_once(dirname(__FILE__) . '/../../class/mqtt.class.php');
require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

$property = $_POST['property'];
$value = $_POST['value'];
if (!is_numeric($value)) {
    exit;
}
$unit = managerUnits::getUnitLabel('kitchen_hood');
if (is_null($unit)) {
    exit;
}
$device = $unit->getDevice();
if (is_null($device)) {
    exit;
}

$topic = 'home/kitchen/vent/cmnd/set'; //TODO - костыль, топик надо хранить в базе

$value = (int)$value;
$payload = ''.$property.';'.$value;

$mqtt = mqttSend::connect();
$mqtt->publish($topic, $payload);

