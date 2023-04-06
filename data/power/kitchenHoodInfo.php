<?php
require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

$unit = managerUnits::getUnitLabel('kitchen_hood');
$valueVent = null;
$dateLastStatus = '';
if (!is_null($unit)) {
    $dataVent = $unit->getData();
    $valueVent = json_decode($dataVent['value'], true);
    $dateLastStatus = $dataVent['date'];
}

if (is_array($valueVent)) {

    $tempOut = $valueVent['tempOut'];
    $humOut = $valueVent['humOut'];
    $tempVent = $valueVent['tempVent'];
    $humVent = $valueVent['humVent'];
    $deltaEnable = $valueVent['deltaEnable'];
    $deltaETemp = $valueVent['deltaETemp'];
    $deltaEHum = $valueVent['deltaEHum'];
    $deltaDisable = $valueVent['deltaDisable'];
    $deltaDTemp = $valueVent['deltaDTemp'];
    $deltaDHum = $valueVent['deltaDHum'];
    $deltaTemp = $tempVent - $tempOut;
    $deltaHum = $humVent - $humOut;

    $infoTemp = 'Температура: кухня '.$tempOut.'&deg, вытяжка '.$tempVent.'&deg, дельта '.$deltaTemp.'&deg';
    $infoHum = 'Влажность: кухня '.$humOut.'%, вытяжка '.$humVent.'%, дельта '.$deltaHum.'%';
    $infoRun = 'Enable: '.$deltaEnable.' Disable:'.$deltaDisable;
    $colorEnable = '#003200';
    if ($deltaEnable) {
        $colorEnable = '#00ff00';
    }
    $colorDisable = '#003200';
    if ($deltaDisable) {
        $colorDisable = '#00ff00';
    }

    echo '<script src="js2/powerKitchenHood.js"></script>';
    echo '            <div style="display: flex">';
    echo '                <div id="power_kitchen_hood_update_info"></div>';
    echo '                <div style="margin-left: 15px">';
    echo '                    <div>'.$infoTemp.'</div>';
    echo '                    <div>'.$infoHum.'</div>';
    echo '                </div>';
    echo '                <div style="margin-left: 10px">';
    echo '                    <div style="display: flex">';
    echo '                      <div style="margin: 2px; width: 15px; height: 7px; background-color: '.$colorEnable.'"></div>';
    echo '                      <div style="margin-left: 5px">вкл</div>';
    echo '                    </div>';
    echo '                    <div style="display: flex">';
    echo '                      <div style="margin: 2px; width: 15px; height: 7px; background-color: '.$colorDisable.'"></div>';
    echo '                      <div style="margin-left: 5px">выкл</div>';
    echo '                    </div>';
    echo '                </div>';
    echo '            </div>';
} else {
    echo '';
}