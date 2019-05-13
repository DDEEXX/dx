<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/lists.class.php');

$sel = new selectOption();
$sel->set('SensorTypeID', typeDevice::HUMIDITY);
$sel->set('Disabled', 0);

$humidityUnits = managerUnits::getListUnits($sel);

foreach ($humidityUnits as $tekUnit) {
    $val = $tekUnit->getValue();
    if (!is_null($val)) {
        $tekUnit->writeValue($val);
    }
}

unset($humidityUnits);


$unit = managerUnits::getUnitLabel('humidity_vault');
$value = $unit->readValue();

var_dump($value);
