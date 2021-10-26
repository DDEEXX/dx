<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:45
 */

sleep(5);

$fileDir = dirname(__FILE__);

//Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
$child_pid = pcntl_fork();
if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем основным процессом дочерний.
posix_setsid();
// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли

require($fileDir."/class/daemon.class.php");
require($fileDir."/class/backgrounder.class.php");

ini_set('error_log',$fileDir.'/logs/errorRunScript.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir.'/logs/application.log', 'ab');
$STDERR = fopen($fileDir.'/logs/daemonRunScript.log', 'ab');

class daemonScripts extends daemon {
    const NAME_PID_FILE = 'runScript.pid';
    const PAUSE = 200000;
    protected $scripts = array('move_hall.php');
    protected $bg;              //Объект для запуска процессов в фоне
    protected $dirScript;       //Путь до каталога со скриптами сценариев

    public function __construct($dirScripts, $dirPidFile) {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
        $this->dirScript = $dirScripts;
        $this->bg = new backgrounder();
    }

    public function run() {

        $this->putPitFile(); // устанавливаем PID файла

        // Пока $stop_server не установится в TRUE, гоняем бесконечный цикл
        while (!$this->stopServer()) {
            for ($i = 0; $i < count($this->scripts); $i++) {
                $nameScript = $this->dirScript . '/' . $this->scripts[$i];
                try {
                    $this->bg->launch($nameScript);
                }
                catch (Exception $e) {

                }
            }

            usleep(self::PAUSE);

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов

        }
    }

}

$daemon = new daemonScripts($fileDir.'/scripts', $fileDir.'/tmp');
if ($daemon->isDaemonActive()) {
    exit();
}
$daemon->run();

