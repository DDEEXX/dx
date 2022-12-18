<?php
/**
 * Опрос датчиков.
 * Опрос датчиков, по расписанию cron (мин 1 раз в минуту).
 * Датчики могут быть 2-х типов:
 * 1. датчики возвращающие значения сразу по запросу
 * 2. датчики, на которые подается запрос, а ответ приходит через некоторое время (mqqt).
 */

require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/managerDevices.class.php');

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::TEMPERATURE);
$temperatureDevice = managerDevices::getListDevices($sel);
foreach ($temperatureDevice as $device) {
    $device->requestData(); //запрос данных с датчика
}
unset($temperatureDevice);

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::HUMIDITY);
$humidityDevice = managerDevices::getListDevices($sel);
foreach ($humidityDevice as $device) {
    $device->requestData(); //запрос данных с датчика
}
unset($humidityDevice);

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::PRESSURE);
$pressureDevice = managerDevices::getListDevices($sel);
foreach ($pressureDevice as $device) {
    $device->requestData(); //запрос данных с датчика
}
unset($pressureDevice);

