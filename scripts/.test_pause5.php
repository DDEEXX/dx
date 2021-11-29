<?php

require_once(dirname(__FILE__). '/../class/logger.class.php');
require_once(dirname(__FILE__). 'move_hall.php');
require_once(dirname(__FILE__). 'move_under_stair.php');

class test_pause5
{
    static function start() {
        logger::writeLog('S 5', loggerTypeMessage::NOTICE, loggerName::DEBUG);
        sleep(5);
        logger::writeLog('F 5', loggerTypeMessage::NOTICE, loggerName::DEBUG);
    }
}

move_hall::start();
move_under_stair::start();
test_pause5::start();
move_stair::start();