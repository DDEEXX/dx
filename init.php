<?php
require_once(dirname(__FILE__) . '/class/sharedMemory.class.php');
require_once(dirname(__FILE__) . '/class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

if (managerSharedMemory::init()){
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

