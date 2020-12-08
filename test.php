<?php

require_once(dirname(__FILE__) . '/class/mqqt.class.php');

$mqqt = mqqt::Connect();

for ($i = 0; $i < 10; $i++) {
   $mqqt->loop();
}

echo '123';

//
//
//require_once(dirname(__FILE__) . '/class/mqqt.class.php');
//
//
//use Mosquitto\Client;
//
//$client = new Mosquitto\Client("dxhome");
//$client->setCredentials('dxhome', '16384');
//$client->onSubscribe('subscribe');
//$client->onMessage('message');
//$client->onDisconnect('disconnect');
//$client->onConnect('connect');
//$client->connect('192.168.1.4', 1883);
//$client->onLog('logger');
//
//$client->subscribe('bath/store/cellar/humidity', 0);
//
//$client->loopForever();
//
////for ($i = 0; $i < 3; $i++) {
////    $client->loop();
////}
////
////$client->unsubscribe('bath/store/cellar/humidity');
//
//
//echo "END";
//
//function connect($r, $message) {
//    echo "I got code {$r} and message {$message}\n";
//}
//
//function disconnect() {
//    echo "Disconnected cleanly\n";
//}
//
//function message($message) {
//    global $client;
//    printf("Got a message on topic %s with payload:\n%s\n", $message->topic, $message->payload);
//    $client->exitLoop();
//}
//
//function logger() {
//    var_dump(func_get_args());
//}
//
//function subscribe() {
//    echo "Subscribed to a topic\n";
//}