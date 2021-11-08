<?php
/**
 * MQTT клиент, реагирует на сообщения, на которые подписан
 * Created by PhpStorm.
 */

//Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
$child_pid = pcntl_fork();
if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем основным процессом дочерний.
posix_setsid();
// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли

$fileDir = dirname(__FILE__);
require($fileDir."/class/daemon.class.php");
require($fileDir."/class/mqtt.class.php");
require_once($fileDir."/class/logger.class.php");

ini_set('error_log',$fileDir.'/logs/errorLoopMQTT.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir.'/logs/application.log', 'ab');
$STDERR = fopen($fileDir.'/logs/daemonLoopMQTT.log', 'ab');

class daemonLoopMQTT extends daemon
{

    const NAME_PID_FILE = 'loopMQTT.pid';
    const PAUSE = 100000; //Пауза в основном цикле, в микросекундах (0.1 сек)
    const PAUSE_RECONNECT = 1000000; //Пауза при подключении, в микросекундах (1 сек)

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run()
    {
        $this->putPitFile(); // устанавливаем PID файла

        $mqtt = new mqttLoop(true, true);
        $mqtt->connect();
        $mqtt->loopForever();

        while (!$this->stopServer()) {

            sleep(1);

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов
        }

        $mqtt->disconnect();
        unset($mqtt);
    }

}

$daemon = new daemonLoopMQTT( $fileDir.'/tmp');
if ($daemon->isDaemonActive()) {
    exit();
}
$daemon->run();
