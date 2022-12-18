<?php
/**
 * Запись данных датчиков в базу данных
 * 1 этап - запрос данных с датчиков
 * 2 этап - просмотр значений, если время значений позже отправки запроса, то сохраняем в базу
 */

require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

$now = time();

require_once(dirname(__FILE__) . '/cronSensors.php');

function saveValueDeviceToDB($device, $now) {
    $deviceID = $device->getDeviceID();
    $deviceData = new deviceData($deviceID);
    $data = $deviceData->getData();
    //если есть показаний и показания после опроса
    if (!$data->valueNull && (int)$data->date >= $now) {
        $sel = new selectOption();
        $sel->set('a.DeviceID', $deviceID);
        $units = managerUnits::getListUnits($sel);
        foreach ($units as $unit) {
            unitsValuesHistory::saveDataToDB($unit, $data);
        }
    }
}

sleep(10); //ждем, пока с датчиков придут данные

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::TEMPERATURE);
$temperatureDevice = managerDevices::getListDevices($sel);
foreach ($temperatureDevice as $device) {
    saveValueDeviceToDB($device, $now);
}
unset($temperatureDevice);

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::HUMIDITY);
$humidityDevice = managerDevices::getListDevices($sel);
foreach ($humidityDevice as $device) {
    saveValueDeviceToDB($device, $now);
}
unset($humidityDevice);

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::PRESSURE);
$pressureDevice = managerDevices::getListDevices($sel);
foreach ($pressureDevice as $device) {
    saveValueDeviceToDB($device, $now);
}
unset($pressureDevice);