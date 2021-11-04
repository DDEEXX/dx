<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/mqqt.class.php');
require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

/**
 * Событие на "нажатие" на модуль с именем $label
 * @param $label
 */
function key_d($label) {
    $unit = managerUnits::getUnitLabel($label);

    if (is_null($unit)) {
        logger::writeLog('Не могу создать объект по метке :: ' . $label,
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return;
    }

    $isLight = $unit->getValue();

    $value = $isLight ? 0 : 1;
    $statusKey = $isLight ? statusKey::OFF : statusKey::OUTSIDE;

    try {
        $unit->updateValue($value, $statusKey);
    }
    catch (Exception $e) {
        $unit->setValue($value, $statusKey);
    }

    unset($unit);
}

function keyCode ($label, $code) {

    $unit = managerUnits::getUnitLabel($label);

    if (is_null($unit)) {
        logger::writeLog('Не могу создать объект по метке :: ' . $label,
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return;
    }

    $topicPublish = $unit->checkMQТTTopicPublish();
    if (is_null($topicPublish)) {
        return;
    }

    $mqtt = mqqt::Connect(false);
    $mqtt->publish($topicPublish, $code);
    unset($mqtt);

}

if (!empty($_REQUEST['label'])) {
    if (!empty($_REQUEST['code'])) {
        keyCode($_REQUEST['label'], $_REQUEST['code']);
    }
    else {
        key_d($_REQUEST['label']);
    }
}
