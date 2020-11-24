<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:45
 */

$fileDir = dirname(__FILE__);

include_once ($fileDir.'/class/daemonScripts.class.php');

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
$STDERR = fopen($fileDir.'/logs/daemonRunScript.log', 'ab');

$daemon = new daemonScripts($fileDir.'/scripts', $fileDir.'/tmp');
if ($daemon->isDaemonActive()) {
    exit();
}
$daemon->putPitFile(getmypid());
$daemon->run();
