<?php
/**
 * Опрос датчиков и запись показаний в базу данных
 * Опрос датчиков, которые могут вернуть свое состояние по запросу в любое время.
 * Этот скрипт, для датчиков, которые сами отправляют свое состояние.
 * Для датчиков, которые надо постоянно "слушать", необходимо использовать скрипт loopForever.php
 */

require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/mqtt.class.php');

$humidityUnits = managerUnits::getListUnits(typeUnit::HUMIDITY, 0);
foreach ($humidityUnits as $unit) {
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
        // TODO: сообщение для отправки надо где-то хранить, лучше в базе данных
        $mqtt->publish($topic, 'humidity');
    }
}
unset($humidityUnits);

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