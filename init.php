<?php
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

//if (managerSharedMemory::init()) {
if (managerUnits::initUnits()) {
    logger::writeLog('Модули инициализированы', loggerTypeMessage::NOTICE ,loggerName::ACCESS);
    try {
        managerDevices::updateAlarmOWireSensorDeviceFromDB();
    } catch (Exception $e) {
        logger::writeLog('Модули не инициализированы, дальнейшая работа может быть с ошибками',
            loggerTypeMessage::ERROR,
            loggerName::ERROR);
    }
}
else {
    logger::writeLog('Модули не инициализированы, дальнейшая работа может быть с ошибками',
                    loggerTypeMessage::ERROR,
                    loggerName::ERROR);
}

