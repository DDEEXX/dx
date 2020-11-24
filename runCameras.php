<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.11.20
 * Time: 17:14
 */

$fileDir = dirname(__FILE__);

include_once ($fileDir.'/class/cameras.class.php');
require_once($fileDir.'/class/sqlDataBase.class.php');

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
$STDERR = fopen($fileDir.'/logs/daemonCameras.log', 'ab');

$cameraImagePath = DB::getConst('CamerasImagePath');
if (isset($cameraImagePath) && $cameraImagePath !== '') { //если пустая строка, выход
    exit();
}

$daemon = new daemonCameras($fileDir.'/tmp', $cameraImagePath);
if ($daemon->isDaemonActive()) {
    exit();
}
$daemon->putPitFile(getmypid());
$daemon->run();
