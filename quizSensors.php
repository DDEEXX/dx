<?php
/**
 * Опрос датчиков и запись показаний в базу данных
 * Опрос датчиков, которые могут вернуть свое состояние по запросу в любое время.
 * Для датчиков, которые сами отправляют свое состояние, и которые надо постоянно "слушать",
 * необходимо использовать скрипт loopForever.php
 */

require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/lists.class.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

$temperatureUnits = managerUnits::getListUnits(typeUnit::TEMPERATURE, 0);
foreach ($temperatureUnits as $unit) {
    if (is_null($unit)) continue;
    //опрашиваем датчики, которые могут вернуть значение в любое время
    if ($unit->getModeDeviceValue() == modeDeviceValue::GET_VALUE) {
        $result = $unit->updateValue(); //Считываем и обновляем данные в объекте модуля
        if (!is_null($result)) {
            $unit->writeCurrentValueDB(); //Записываем данные в базу данных
        }
    }
}
unset($temperatureUnits);

//$sel = new selectOption();
//$sel->set('DeviceTypeID', typeDevice::PRESSURE);
//$sel->set('Disabled', 0);
//
//$pressureUnits = managerUnits::getListUnitsDB($sel);
//
//foreach ($pressureUnits as $tekUnit) {
//    $val = $tekUnit->getValue();
//    if (!is_null($val)) {
//        $tekUnit->writeValue($val);
//    }
//}
//
//unset($pressureUnits);
//
//$sel = new selectOption();
//$sel->set('DeviceTypeID', typeDevice::HUMIDITY);
//$sel->set('Disabled', 0);
//
//$humidityUnits = managerUnits::getListUnitsDB($sel);
//
//foreach ($humidityUnits as $tekUnit) {
//    $val = $tekUnit->getValue();
//    if (!is_null($val)) {
//        $tekUnit->writeValue($val);
//    }
//}
//
//unset($humidityUnits);
