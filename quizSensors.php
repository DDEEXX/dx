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
require_once(dirname(__FILE__) . '/class/mqtt.class.php');

$temperatureUnits = managerUnits::getListUnits(typeUnit::TEMPERATURE, 0);
foreach ($temperatureUnits as $unit) {
    if (is_null($unit)) continue;
    //опрашиваем датчики, которые могут вернуть значение в любое время
    if ($unit->getModeDeviceValue() == modeDeviceValue::GET_VALUE) {
        $value = $unit->getValueFromDevice();
        if (!is_null($value)) {
            $unit->updateValue($value);   //обновляем значение в модуле
            $unit->writeCurrentValueDB(); //Записываем данные в базу данных
        }
    }
    $topic = $unit->checkMqttTopicPublish();
    if (!is_null($topic)) {
        $mqtt = mqttSend::connect();
        $mqtt->publish($topic, 'temperature');
        //после публикации температура должна вернуться в модуль и записаться в базу данных
        //эти действия делаются в обработке подписки
    }
}
unset($temperatureUnits);

$pressureUnits = managerUnits::getListUnits(typeUnit::PRESSURE, 0);
foreach ($pressureUnits as $unit) {
    if (is_null($unit)) continue;
    if ($unit->getModeDeviceValue() == modeDeviceValue::GET_VALUE) {
        $value = $unit->getValueFromDevice();
        if (!is_null($value)) {
            $unit->updateValue($value);   //обновляем значение в модуле
            $unit->writeCurrentValueDB(); //Записываем данные в базу данных
        }
    }
    $topic = $unit->checkMqttTopicPublish();
    if (!is_null($topic)) {
        $mqtt = mqttSend::connect();
        $mqtt->publish($topic, 'pressure');
    }
}
unset($pressureUnits);

//
//$sel = new selectOption();
//$sel->set('DeviceTypeID', typeDevice::HUMIDITY);
//$sel->set('Disabled', 0);
//
//$humidityUnits = managerUnits::getListUnitsDB($sel);
//
//foreach ($humidityUnits as $tekUnit) {
//    $val = $tekUnit->getValueFromDevice();
//    if (!is_null($val)) {
//        $tekUnit->writeValue($val);
//    }
//}
//
//unset($humidityUnits);
