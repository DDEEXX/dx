<?php
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

if (managerUnits::initUnits()){
    logger::writeLog("Модули иницилизованы", loggerTypeMessage::NOTICE ,loggerName::ACCESS);
}
else {
    logger::writeLog("Модули не иницилизованы, дальнейшая работа может быть с ошибками",
                    loggerTypeMessage::ERROR,
                    loggerName::ERROR);
}

