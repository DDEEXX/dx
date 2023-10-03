<?php
/**
 * Тестирование физических устройств, результат тестирования записывается с таблицу deviceTest
 */

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/../class/mqtt.class.php');

//One-Wire
$sel = new selectOption();
$sel->set('NetTypeID', netDevice::ONE_WIRE);
$devices = managerDevices::getListDevices($sel);
//foreach ($devices as $device) {
//    $testCode = $device->test(); //запрос данных с датчика
//    managerDevices::updateTestCode($device, $testCode);
//}

//MQTT
$mqttTest = mqttTest::connect();
$sel = new selectOption();
$sel->set('NetTypeID', netDevice::ETHERNET_MQTT);
$devices = managerDevices::getListDevices($sel);
foreach ($devices as $device) {
    $mqttTest->loop();
    $device->test(); //запрос данных с датчика
    $mqttTest->loop();
}

const TIME_TEST_LOOP = 10; // время в течение которого происходит опрос по mqtt
$timeBegin = time();
$stop = false;
while (!$stop) {
    $now = time();
    $mqttTest->loop();
    if ($now-$timeBegin > TIME_TEST_LOOP) {
        $stop = true;
    }
}

//содержит только ответившие устройства и их "ответ", если устройство не ответило в массиве его нет.
$codeDevices = $mqttTest->getTestCodes();

$now = time();

foreach ($devices as $device) {
    $deviceId = $device->getDeviceID();
    $devicePhysic = $device->getDevicePhysic();
    if ($devicePhysic instanceof iDevicePhysicMQTT ) {
        $topicTest = $devicePhysic->getTopicTest();

        if (empty($topicTest)) { //топика для тестирования нет, считаем условно рабочий
            managerDevices::updateTestCode($device, testDeviceCode::NO_TEST, $now);
            continue;
        }

        if (is_a($devicePhysic, 'switchWHD02_MQTT')) { //TODO - затычка всегда считается рабочим
            if (array_key_exists($deviceId, $codeDevices)) {
                $codeDevices[$deviceId] = 0;
            }
        }
        if (array_key_exists($deviceId, $codeDevices)) {
            $testDeviceCode = $devicePhysic->formatTestPayload($codeDevices[$deviceId]);
            managerDevices::updateTestCode($device, $testDeviceCode, $now);
        } else {
            managerDevices::updateTestCode($device, testDeviceCode::NO_CONNECTION, $now);
        }
    } else {
        managerDevices::updateTestCode($device, testDeviceCode::NO_CONNECTION, $now);
    }
}
