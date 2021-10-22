<?php

require_once(dirname(__FILE__) . "/daemon.class.php");
require_once(dirname(__FILE__) . "/mqqt.class.php");

class daemonLoopMQQT extends daemon
{
    const NAME_PID_FILE = 'loopMQQT.pid';
    protected $stop_server = FALSE;

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run()
    {

        $mqqt = mqqt::Connect(true);

        while (true) {

            if ($this->stop_server) {
                break;
            }

            $mqqt->loop();

        }
    }
}