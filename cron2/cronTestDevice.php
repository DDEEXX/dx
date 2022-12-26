<?php
/**
 * Тестирование физических устройств, результат тестирования записывается с таблицу deviceTest
 */

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/../class/mqtt.class.php');

$sel = new selectOption();
$sel->set('NetTypeID', netDevice::ONE_WIRE);
$devices = managerDevices::getListDevices($sel);
foreach ($devices as $device) {
    $testCode = $device->test(); //запрос данных с датчика
    managerDevices::updateTestCode($device, $testCode);
}

$mqttTest = mqttTest::connect();

$sel = new selectOption();
$sel->set('NetTypeID', netDevice::ETHERNET_MQTT);
$devices = managerDevices::getListDevices($sel);
foreach ($devices as $device) {
    $mqttTest->loop();
    $testCode = $device->test(); //запрос данных с датчика
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

$codeDevices = $mqttTest->getTestCodes();

$now = time();

foreach ($devices as $device) {
    $deviceId = $device->getDeviceID();
    if (array_key_exists($deviceId, $codeDevices)) {
        managerDevices::updateTestCode($device, $codeDevices[$deviceId], $now);
    }
    else {
        managerDevices::updateTestCode($device, testDeviceCode::NO_CONNECTION, $now);
    }
}
