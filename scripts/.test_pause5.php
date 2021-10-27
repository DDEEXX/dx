<?php

require_once(dirname(__FILE__)."/../class/logger.class.php");

class test_pause5
{
    static function start() {
        logger::writeLog("S 5", loggerTypeMessage::NOTICE, loggerName::DEBUG);
        sleep(5);
        logger::writeLog("F 5", loggerTypeMessage::NOTICE, loggerName::DEBUG);
    }
}