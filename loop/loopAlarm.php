<?php
/**
 * Постоянный анализ данных в базе и запуск соответствующего сценария по отработке включении и отключении тревоги
 */

sleep(10);

//Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
$child_pid = pcntl_fork();
if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем основным процессом дочерний.
posix_setsid();
// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли

$fileDir = dirname(__FILE__).'/..';

require($fileDir . '/class/daemon.class.php');
require($fileDir . '/class/managerDevices.class.php');

ini_set('error_log', $fileDir . '/logs/errorLoopAlarm.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir . '/logs/application.log', 'ab');
$STDERR = fopen($fileDir . '/logs/daemonLoopAlarm.log', 'ab');

class daemonLoopAlarm extends daemon
{
    const NAME_PID_FILE = 'loopAlarm.pid';
    const PAUSE = 3; //Пауза в основном цикле, в секундах

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    private function alarm()
    {

    }

    public function run()
    {
        parent::run();

        while (!$this->stopServer()) {

            $this->alarm();

            sleep(self::PAUSE); //ждем

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов
        }
    }
}

$daemon = new daemonLoopAlarm($fileDir . '/tmp');
$daemonActive = $daemon->isDaemonActive();
if ($daemonActive == 0) {
    $daemon->run();
} else {
    logger::writeLog('Невозможно запустить демона daemonLoopAlarm, код возврата - ' . $daemonActive,
        loggerTypeMessage::ERROR, loggerName::ERROR);
}
