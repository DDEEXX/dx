<?php
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/sharedMemory.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');
require_once(dirname(__FILE__) . '/class/sqlDataBase.class.php');

sleep(10);

$connectToDB = false;
while (!$connectToDB) {
    try {
        $con = sqlDataBase::Connect();
        if (mysqli_ping($con->getConnect())) {
            logger::writeLog('Доступ к базе данных присутствует',
                loggerTypeMessage::NOTICE ,loggerName::ACCESS);
            $connectToDB = true;
            unset($con);
        }
    } catch (connectDBException $e) {
        logger::writeLog('Нет доступа к базе данных',
            loggerTypeMessage::WARNING ,loggerName::ACCESS);
    }
    sleep(2);
}

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

$dir = dirname(__FILE__). '/';
exec('php '.$dir.'loop/loopMQTTfast.php &');
exec('php '.$dir.'loop/loopMQTT.php &');
exec('php '.$dir.'loop/loopMQTTalarm.php &');
exec('php '.$dir.'loop/loopMQTTtest.php &');
exec('php '.$dir.'loop/loopForever.php &');
exec('php '.$dir.'loop/loopHeating.php &');
exec('php '.$dir.'loop/loopAlice.php &');
exec('php '.$dir.'loop/runScripts.php &');