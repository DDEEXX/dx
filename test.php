<?php

//$client = new Mosquitto\Client();
//$client->onConnect('connect');
//$client->onDisconnect('disconnect');
//$client->onPublish('publish');
//$client->setCredentials("dxhome", "16384");
//$client->connect("192.168.1.4", 1883, 5);
//
//while (true) {
//    try{
//        $client->loop();
//        $mid = $client->publish('bath/store/cellar/humidity', "DEX");
//        //$client->loop();
//        $client->disconnect();
//    }catch(Mosquitto\Exception $e){
//        echo "ERROR\n";
//        echo $e->getMessage();
//        echo "\n";
//        return;
//    }
//    sleep(2);
//}
//
//$client->disconnect();
//unset($client);
//
//function connect($r) {
//    echo "I got code {$r}\n";
//}
//
//function publish() {
//    global $client;
//    echo "Mesage published\n";
//    $client->disconnect();
//}
//
//function disconnect() {
//    echo "Disconnected cleanly\n";
//}

$fileDir = dirname(__FILE__);

include_once ($fileDir.'/class/daemonScripts.class.php');

 //Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
//$child_pid = pcntl_fork();
//if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
//    exit();
//}
//// Делаем основным процессом дочерний.
//posix_setsid();
//// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли
//
//ini_set('error_log',$fileDir.'/logs/error.log');
//fclose(STDIN);
//fclose(STDOUT);
//fclose(STDERR);
//$STDIN = fopen('/dev/null', 'r');
//$STDOUT = fopen($fileDir.'/logs/application.log', 'ab');
//$STDERR = fopen($fileDir.'/logs/daemonRunScript.log', 'ab');

$daemon = new daemonScripts($fileDir.'/scripts', $fileDir.'/tmp');
if ($daemon->isDaemonActive()) {
    exit();
}
$daemon->putPitFile(getmypid());
$daemon->run();
