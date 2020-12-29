<?php
/**
 * Опрос датчиков и запись показаний в базу данных
 * Опрашиваются только те датчики, которые могут в любой момент времени вернуть свое состояние.
 * Для датчикав, которые сами отправляют свое состояние, и которые надо постоянно "слушать",
 * необходимо искользовать скрипт loopForever.php
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:23
 */

require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/lists.class.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

//$sel = new selectOption();
//$sel->set('DeviceTypeID', typeDevice::TEMPERATURE);
//$sel->set('Disabled', 0);
//$temperatureUnits = managerUnits::getListUnitsDB($sel);
//foreach ($temperatureUnits as $tekUnit) {
//    $val = $tekUnit->getValue();
//    if (!is_null($val)) {
//        $tekUnit->writeValue($val);
//    }
//}
//unset($temperatureUnits);

$temperatureUnits = managerUnits::getListUnits(typeUnit::TEMPERATURE, 0);
foreach ($temperatureUnits as $unit) {
    if (is_null($unit)) continue;
    //опрашиваем датчики которые могут вернуть значение в любое время
    if ($unit->getTypeDeviceNet() == typeDeviceNet::GET_VALUE) {
        $result = $unit->updateValue(); //Считываем и обновляем данные в обекте модуля
        if (!is_null($result)) {
            $unit->writeValueDB($result); //Записываем данные в базу данных
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
