<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

function key_d($label)
{
    try {
        $unit = managerUnits::getUnitLabel($label);
    } catch (connectDBException $e) {
        logger::writeLog('Ошибка в модуле powerKey.php при подключении в базе данных.', logger::FATAL);
        return;
    } catch (querySelectDBException $e) {
        logger::writeLog('Ошибка в модуле powerKey.php при выполнее запроса в базе данных.', logger::FATAL);
        return;
    }

    if (!empty($unit)) {

        $isLight = $unit->getValue();

        if ($isLight) {
            $unit->setValue(0, statusKey::OFF);
        }
        else {
            $unit->setValue(1, statusKey::OUTSIDE);
        }

        unset($ow);

    }
    else {
        logger::writeLog('Не могу создать объект по метке :: ' . $label, logger::FATAL);
    }

    unset($unit);
}


if (!empty($_REQUEST['label'])) {
    key_d($_REQUEST['label']);
}
