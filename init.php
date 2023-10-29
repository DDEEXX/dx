<?php
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/sharedMemory.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');
require_once(dirname(__FILE__) . '/class/sqlDataBase.class.php');

$resInitConst = managerSharedMemory::initConst();
$resInitUnits = managerUnits::initUnits();

if ($resInitConst && $resInitUnits) {
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

while (true) {
    try {
        $con = sqlDataBase::Connect();
        unset($con);
        break;
    } catch (connectDBException $e) {
        sleep(2);
    }
}

exec("nohup php loopMQTTfast.php &");
exec("nohup php loopMQTT.php &");