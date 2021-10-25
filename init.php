<?php
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

if (managerUnits::initUnits()){
    logger::writeLog("Модули инициализированы", loggerTypeMessage::NOTICE ,loggerName::ACCESS);
    managerUnits::setAlias1WireUnit();
}
else {
    logger::writeLog("Модули не инициализированы, дальнейшая работа может быть с ошибками",
                    loggerTypeMessage::ERROR,
                    loggerName::ERROR);
}

