<?php
/**
 * MQTT клиент, перевод команд yandex2mqtt
 * Created by PhpStorm.
 */

//Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
$child_pid = pcntl_fork();
if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем дочерний процесс основным.
posix_setsid();
// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли

$fileDir = dirname(__FILE__).'/..';
require($fileDir . '/class/daemon.class.php');
require($fileDir . '/class/mqtt.class.php');

ini_set('error_log',$fileDir.'/logs/errorLoopAlice.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir.'/logs/application.log', 'ab');
$STDERR = fopen($fileDir.'/logs/daemonLoopAlice.log', 'ab');

class daemonLoopAlice extends daemon
{

    const NAME_PID_FILE = 'loopAlice.pid';
    const PAUSE = 100000; //Пауза в основном цикле, в микросекундах (0.1 сек)
    const INTERVAL_UPDATE_SUBSCRIBE = 600; //интервал обновления подписок в секундах

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run()
    {
        parent::run();

        $mqtt = new mqttAlice();
        $mqtt->connect();
        $previousUpdateSubscibe = time();

        while (!$this->stopServer()) {
            $mqtt->loop();

            $now = time();
            if ($now - $previousUpdateSubscibe > self::INTERVAL_UPDATE_SUBSCRIBE) {
                $mqtt->updateSubscribe();
                $previousUpdateSubscibe = $now;
            } else {
                usleep(self::PAUSE);
            }

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов
        }

        $mqtt->disconnect();
        unset($mqtt);
    }

}

$daemon = new daemonLoopAlice( $fileDir.'/tmp');
if ($daemon->isDaemonActive()) {
    exit();
}
$flag = 1;
$mes = '';
while ($flag != 0) {
    logger::writeLog('Подключение к MQTT брокеру (из loopAlice). Попытка '.$flag, loggerTypeMessage::NOTICE, loggerName::MQTT);
    try {
        $daemon->run();
        $flag = 0;
    }
    catch (Exception $e) {
        $flag++;
        sleep(2);
    }
    if ($flag>10) {
        $flag = 0;
        $mes = 'Не удалось подключиться к MQTT брокеру (из loopAlice). Проверьте параметры подключения и доступность брокера.';
    }
}
if (strlen($mes) > 0 ) {
    logger::writeLog($mes, loggerTypeMessage::ERROR, loggerName::MQTT);
    logger::writeLog($mes, loggerTypeMessage::ERROR, loggerName::ERROR);
}

