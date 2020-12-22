<?php
/**
 * Постоянный опрос датчиков, которые сами отправляют свое состояние.
 * Например, датчики по протоколу MQTT, датчики 1-wire опрашиваемые по команде Read Conditional Search ROM
 * Created by PhpStorm.
 */

sleep(5);

$fileDir = dirname(__FILE__);

include_once ($fileDir.'/class/daemonLoopForever.class.php');

//Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
$child_pid = pcntl_fork();
if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем основным процессом дочерний.
posix_setsid();
// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли

ini_set('error_log',$fileDir.'/logs/error.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir.'/logs/application.log', 'ab');
$STDERR = fopen($fileDir.'/logs/daemonLoopForever.log', 'ab');

$daemon = new daemonLoopForever( $fileDir.'/tmp');
if ($daemon->isDaemonActive()) {
    exit();
}
$daemon->putPitFile(getmypid());
$daemon->run();
