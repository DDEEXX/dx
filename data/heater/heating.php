<?php

if (!isset($_REQUEST['dev'])) return;

require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../../class/mqtt.class.php');

if ($_REQUEST['dev'] == 'boiler') {

    $label = $_REQUEST['label'];
    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return;
    }
    $unitData = $unit->getData();
    $value = $unitData->value;
    $value->date = $unitData->date;

    header('Content-Type: application/json');
    echo json_encode($value);
}
elseif ($_REQUEST['dev'] == 'check_boilerStatus') {
    $result = ['update' => false];
    $label = $_REQUEST['label'];
    $dateStatus = (int)$_REQUEST['dateStatus'];
    $unit = managerUnits::getUnitLabel($label);
    if (!is_null($unit)) {
        $unitData = $unit->getData();
        $dateLastStatus = $unitData->date;
        if ($dateStatus != $dateLastStatus) {
            $result['update'] = true;
        }
    }
    header('Content-Type: application/json');
    echo json_encode($result);
}
else if ($_REQUEST['dev'] == 'set') {

    $label = $_REQUEST['label'];
    $p = $_REQUEST['p'];
    $v = $_REQUEST['v'];
    $d = isset($_REQUEST['d']) && is_numeric($_REQUEST['d']) ? (int)$_REQUEST['d'] : 1;

    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return;
    }
    $curData = $unit->getData();
    $curV = $curData->value->{$p};
    if (!is_numeric($v)) return;
    else $value = (float)($v/$d);
    if ($curV == $value) return;
    $device = $unit->getDevice();
    if (is_null($device)) exit;
    $devicePhysic = $device->getDevicePhysic();
    $topic = $devicePhysic->getTopicSet();
    if (!strlen($topic)) exit;
    $payload = json_encode([$p => $value]);
    $mqtt = mqttSend::connect();
    $mqtt->publish($topic, $payload);
}
