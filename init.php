<?php
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/sharedMemory.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');
require_once(dirname(__FILE__) . '/class/sqlDataBase.class.php');

$i = 50;
while ($i>0) {
    try {
        $con = sqlDataBase::Connect();
        unset($con);
        break;
    } catch (connectDBException $e) {
        sleep(2);
        $i--;
    }
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
exec('php '.$dir.'loopMQTTfast.php &');
exec('php '.$dir.'loopMQTT.php &');
exec('php '.$dir.'loopMQTTalarm.php &');
exec('php '.$dir.'loopMQTTtest.php &');
exec('php '.$dir.'loopForever.php &');
exec('php '.$dir.'runScripts.php &');
//exec("nohup php loopHeating.php &");