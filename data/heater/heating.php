<?php

if (!isset($_REQUEST['dev'])) return;

require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

if ($_REQUEST['dev'] == 'boiler') {

    $label = $_REQUEST['label'];
    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return;
    }
    $unitData = $unit->getData();
    $value = $unitData->value;
    $lastDate = $unitData->date;

    header('Content-Type: application/json');
    echo json_encode($value);
}
