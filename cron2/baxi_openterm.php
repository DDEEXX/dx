<?php
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../class/mqtt.class.php');

$mqtt = mqttSend::connect();

$unitTemperatureOut = managerUnits::getUnitLabel('temp_out_2');
$temperatureOut = $unitTemperatureOut->getData();
$dateValue = $temperatureOut->date;
if (!$temperatureOut->valueNull && time() - $dateValue < 1200) {
    $topic = 'baxi_open/controller/set';
    $payload = sprintf('{"eout":%s}', $temperatureOut->value);
    $mqtt->publish($topic, $payload);
}

sleep(1);
$unitTemperatureIn = managerUnits::getUnitLabel('temp_bedroom');
$temperatureIn = $unitTemperatureIn->getData();
$dateValue = $temperatureIn->date;
if (!$temperatureIn->valueNull && time() - $dateValue < 1200) {
    $topic = 'baxi_open/controller/set';
    $payload = sprintf('{"ein":%s}', $temperatureIn->value);
    $mqtt->publish($topic, $payload);
}

sleep(1);
$unitTemperatureBoilerIn = managerUnits::getUnitLabel('temp_heater_boiler_in');
$temperatureBoilerIn = $unitTemperatureBoilerIn->getData();
$dateValue = $temperatureBoilerIn->date;
if (!$temperatureBoilerIn->valueNull && time() - $dateValue < 1200) {
    $topic = 'baxi_open/controller/set';
    $payload = sprintf('{"retb":%s}', $temperatureBoilerIn->value);
    $mqtt->publish($topic, $payload);
}