<?php
/** Опрос всех температурных датчиков и запись показаний в базу данных
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:23
 */

require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/lists.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

$sel = new selectOption();
$sel->set('SensorTypeID', typeDevice::TEMPERATURE);
$sel->set('Disabled', 0);

$temperatureUnits = managerUnits::getListUnits($sel);

foreach ($temperatureUnits as $tekUnit) {
    logger::writeLog('опрос датчика температуры ID='.$tekUnit->getId());
    $val = $tekUnit->getValue();
    logger::writeLog('значение val='.var_dump($val));
    if (!is_null($val)) {
        logger::writeLog('запись в базу');
        $tekUnit->writeValue($val);
    }
}

unset($temperatureUnits);

$sel = new selectOption();
$sel->set('SensorTypeID', typeDevice::PRESSURE);
$sel->set('Disabled', 0);

$pressureUnits = managerUnits::getListUnits($sel);

foreach ($pressureUnits as $tekUnit) {
    logger::writeLog('опрос датчика давления ID='.$tekUnit->getId());
    $val = $tekUnit->getValue();
    logger::writeLog('значение val='.var_dump($val));
    if (!is_null($val)) {
        logger::writeLog('запись в базу');
        $tekUnit->writeValue($val);
    }
}

unset($pressureUnits);

$sel = new selectOption();
$sel->set('SensorTypeID', typeDevice::HUMIDITY);
$sel->set('Disabled', 0);

$humidityUnits = managerUnits::getListUnits($sel);

foreach ($humidityUnits as $tekUnit) {
    logger::writeLog('опрос датчика влажности ID='.$tekUnit->getId());
    $val = $tekUnit->getValue();
    logger::writeLog('значение val='.var_dump($val));
    if (!is_null($val)) {
        logger::writeLog('запись в базу');
        $tekUnit->writeValue($val);
    }
}

unset($humidityUnits);
