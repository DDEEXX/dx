<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

/**
 * Событие на "нажатие" на модуль с именем $label
 * @param $label
 */
function key_d($label)
{
    $unit = managerUnits::getUnitLabel($label);

    if (is_null($unit)) {
        logger::writeLog('Не могу создать объект по метке :: ' . $label,
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return;
    }

    $isLight = $unit->getValue();

    if ($isLight) {
        $unit->setValue(0, statusKey::OFF);
    }
    else {
        $unit->setValue(1, statusKey::OUTSIDE);
    }

    unset($unit);
}

if (!empty($_REQUEST['label'])) {
    key_d($_REQUEST['label']);
}
