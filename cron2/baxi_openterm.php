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


$unitTemperatureIn = managerUnits::getUnitLabel('temp_bedroom');
$temperatureIn = $unitTemperatureIn->getData();
$dateValue = $temperatureIn->date;
if (!$temperatureIn->valueNull && time() - $dateValue < 1200) {
    $topic = 'baxi_open/controller/set';
    $payload = sprintf('{"ein":%s}', $temperatureIn->value);
    $mqtt->publish($topic, $payload);
}

$unitTemperatureBoilerIn = managerUnits::getUnitLabel('temp_heater_boiler_in');
$temperatureBoilerIn = $unitTemperatureBoilerIn->getData();
echo var_dump($temperatureBoilerIn);
$dateValue = $temperatureBoilerIn->date;
if (!$temperatureBoilerIn->valueNull && time() - $dateValue < 1200) {
    $topic = 'baxi_open/controller/set';
    $payload = sprintf('{"retb":%s}', $temperatureBoilerIn->value);
    $mqtt->publish($topic, $payload);
}

$unitePressure = managerUnits::getUnitLabel('pressure');
$pressure = $unitePressure->getData();
echo var_dump($pressure);
$dateValue = $pressure->date;
echo var_dump(time() - $dateValue);
if (!$pressure->valueNull && time() - $dateValue < 1200) {
    echo var_dump('pressure');
    $topic = 'baxi_open/controller/set';
    $payload = sprintf('{"presb":%s}', $pressure->value);
    $mqtt->publish($topic, $payload);
}
