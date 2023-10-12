<?php
/**
 * Запись данных датчиков в базу данных
 * 1 этап - запрос данных с датчиков
 * 2 этап - просмотр значений, если время значений позже отправки запроса, то сохраняем в базу
 */

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');

function saveValueDeviceToDB($device) {
    $deviceID = $device->getDeviceID();
    $data = $device->getData();
    //если есть показаний
    if (!$data->valueNull) {
        $sel = new selectOption();
        $sel->set('a.DeviceID', $deviceID);
        $units = managerUnits::getListUnits($sel);
        foreach ($units as $unit) {
            unitsValuesHistory::saveDataToDB($unit, $data);
        }
    }
}

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::TEMPERATURE);
$temperatureDevice = managerDevices::getListDevices($sel);
foreach ($temperatureDevice as $device) {
    saveValueDeviceToDB($device);
}
unset($temperatureDevice);

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::HUMIDITY);
$humidityDevice = managerDevices::getListDevices($sel);
foreach ($humidityDevice as $device) {
    saveValueDeviceToDB($device);
}
unset($humidityDevice);

$sel = new selectOption();
$sel->set('Disabled', 0);
$sel->set('DeviceTypeID', typeDevice::PRESSURE);
$pressureDevice = managerDevices::getListDevices($sel);
foreach ($pressureDevice as $device) {
    saveValueDeviceToDB($device);
}
unset($pressureDevice);