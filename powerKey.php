<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/mqtt.class.php');
require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

/**
 * Событие на "нажатие" на модуль с именем $label
 * @param $label
 */
function key_d($unit) {

    $isLight = $unit->getData();

    $value = $isLight ? 0 : 1;
    $statusKey = $isLight ? statusKey::OFF : statusKey::WEB;

    try {
        $unit->updateValue($value, $statusKey);
    }
    catch (Exception $e) {
        $unit->setValue($value, $statusKey);
    }

    unset($unit);
}

$label = null;
$value = null;
$status = null;

if (isset($_REQUEST['label'])) { $label = $_REQUEST['label'];}

if (!is_null($label)) {

    if ($_REQUEST['value']) {$value = $_REQUEST['value']; }
    if ($_REQUEST['status']) {$status = $_REQUEST['status']; }

    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) {
        logger::writeLog('Не могу создать объект по метке :: ' . $label,
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return;
    }

    if (is_null($value)) {$value = 0;}
    $data['value'] = $value;
    if (!is_null($status)) {
        $data['status'] = $status;
    }

    if ($unit instanceof iModuleUnite) {
        $data = json_encode($data);
        $unit->setData($data);
    }

}
