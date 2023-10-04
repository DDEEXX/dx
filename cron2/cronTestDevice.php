<?php
/**
 * Запуск по cron раз в 5 минут
 * Тестирование физических устройств, результат тестирования записывается с таблицу deviceTest
 */

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/../class/mqtt.class.php');

//One-Wire
$sel = new selectOption();
$sel->set('NetTypeID', netDevice::ONE_WIRE);
$devices = managerDevices::getListDevices($sel);
foreach ($devices as $device) {
    $testCode = $device->test(); //запрос данных с датчика
    managerDevices::updateTestCode($device, $testCode);
}

//опрос активности по MQTT
$sel = new selectOption();
$sel->set('NetTypeID', netDevice::ETHERNET_MQTT);
$devices = managerDevices::getListDevices($sel);
foreach ($devices as $device) {
    $device->test(); //запрос данных с датчика
}

//ждем 15 секунд, чтобы пришел и обработался ответ от всех датчиков
sleep(15);

//проверка - если последний отклик меньше чем 10 минут назад, то считаем что не в сети
$now = time();
const MAX_INTERVAL = 600;
foreach ($devices as $device) {
    $lastAvailability = managerDevices::getLastAvailability($device);

    if (is_null($lastAvailability)) {
        managerDevices::updateTestCode($device, testDeviceCode::NO_CONNECTION, $now);
    } else if ($now - $lastAvailability >= MAX_INTERVAL) {
        managerDevices::updateTestCode($device, testDeviceCode::NO_CONNECTION, $now);
    }
}
