<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:45
 */

$fileDir = dirname(__FILE__);

//Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
$child_pid = pcntl_fork();
if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем основным процессом дочерний.
posix_setsid();
// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли

require_once($fileDir . '/class/daemon.class.php');
require_once($fileDir . '/class/logger.class.php');

ini_set('error_log',$fileDir.'/logs/errorRunScript.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir.'/logs/application.log', 'ab');
$STDERR = fopen($fileDir.'/logs/daemonRunScript.log', 'ab');

class daemonScripts extends daemon {
    const NAME_PID_FILE = 'runScript.pid';
    const PAUSE = 100000; // 0.1 секунда
    private $scripts;

    public function __construct($scripts, $dirPidFile) {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
        $this->scripts = $scripts;
    }

    public function run() {

        $this->putPitFile(); // устанавливаем PID файла

        // Пока $stop_server не установится в TRUE, гоняем бесконечный цикл
        while (!$this->stopServer()) {

            foreach ($this->scripts as $script=>$val) {

                if ($val) { //процесс еще работает
                    continue;
                }

                $pid = pcntl_fork(); //плодим дочерний процесс
                if ($pid) { //процесс создан
                    $this->scripts[$script] = $pid;
                } else { //тут рабочая нагрузка
                    $nameClass = substr($script, 0, -4); //убираем расширение ".php"
                    $nameClass::start();
                    exit;
                }
            }

            // ждем пока какой-то процесс не завершится
            while ($signaled_pid = pcntl_waitpid(-1, $status, WNOHANG)) {
                //проверяем какой дочерний процесс завершился
                if ($signaled_pid > 0 ) {
                    $key = array_search($signaled_pid, $this->scripts, true);
                    if ($key !== false) {
                        $this->scripts[$key] = 0;
                    }
                }
                else { //$signaled_pid = -1 все процессы завершились одновременно, просто выходим
                    break;
                }
            }

            usleep(self::PAUSE); //После завершения скрипта, пауза (для всех одинаковая)

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов

        }
    }

}

function getNamesScrips($dirScripts) {

    $result = array();

    if (!is_dir($dirScripts)) {
        logger::writeLog('Не обнаружена папка со скриптами');
    }
    else {
        $cdir = scandir($dirScripts);
        foreach ($cdir as $value) {
            if (!in_array($value, ['.', '..'])) {
                if (!is_dir($dirScripts . DIRECTORY_SEPARATOR . $value)) {
                    if (substr($value, 0, 1) != '.') {//файлы начинающиеся с точки, игнорируются
                        $result[$value] = 0;
                    }
                }
            }
        }
    }

    return $result;

}

$dirScripts = $fileDir.'/scripts';
$scripts = getNamesScrips($dirScripts);

foreach ($scripts as $key=>$value) {
    require_once($dirScripts.DIRECTORY_SEPARATOR.$key);
}

$daemon = new daemonScripts($scripts, $fileDir.'/tmp');
if ($daemon->isDaemonActive()) {
    exit();
}
$daemon->run();

