<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 03.11.20
 * Time: 16:18
 */

require_once(dirname(__FILE__) . "/backgrounder.class.php");
require_once(dirname(__FILE__) . "/daemon.class.php");

class  daemonScripts extends daemon {
    const NAME_PID_FILE = 'runScript.pid';
    const SCRIPT_PAUSE = 100000;
    protected $scripts = array('move_hall.php');
    protected $stop_server = FALSE;
    protected $bg;              //Объект для запуска процессов в фоне
    protected $dirScript;       //Путь до каталога со скриптами сценариев

    public function __construct($dirScripts, $dirPidFile) {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
        $this->dirScript = $dirScripts;
        $this->bg = new backgrounder();
    }

    public function run() {
        // Пока $stop_server не установится в TRUE, гоняем бесконечный цикл
        while (!$this->stop_server) {
            usleep(self::SCRIPT_PAUSE);
            for ($i = 0; $i < count($this->scripts); $i++) {
                $nameScript = $this->dirScript . '/' . $this->scripts[$i];
                try {
                    $this->bg->launch($nameScript);
                }
                catch (Exception $e) {

                }
            }
        }
    }



}