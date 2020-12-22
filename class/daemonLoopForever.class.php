<?php

require_once(dirname(__FILE__) . "/daemon.class.php");

class daemonLoopForever extends daemon
{
    const NAME_PID_FILE = 'loopForever.pid';
    const INTERVAL = 10; //Интервал опроса камер в секундах
    protected $stop_server = FALSE;
    protected $namePidFile;     //Полный путь до pid файла

    public function __construct($dirPidFile) {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run() {

        $start = microtime(true);
        for ($i = 0; true; ++$i) {
            if ($this->stop_server) {
                break;
            }




            usleep(100000); //ждем 0.1 секунду
        }
    }
}