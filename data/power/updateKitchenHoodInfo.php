<?php
require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

$unit = managerUnits::getUnitLabel('kitchen_hood');
if (!is_null($unit)) {
    $device = $unit->getDevice();
    if (!is_null($device)) {
        $device->requestData();
    }

}

