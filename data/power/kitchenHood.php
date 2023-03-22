<?php
$unit = managerUnits::getUnitLabel('kitchen_hood');
$dataVent = $unit->getData();
$valueVent = json_decode($dataVent['value'], true);
$dateLastStatus = $dataVent['date'];

$unit = managerUnits::getUnitLabel('light_kitchen_vent');
$valueLight = 'off';
if (!is_null($unit)) {
    $dataLight = json_decode($unit->getData(), true);
    if (!is_null($dataLight)) {
        $valueNull = $dataLight['valueNull'];
        if (!$valueNull) {
            $valueLight = (int)$dataLight['value'] > 0 ? 'on' : 'off';
        }
    }
}

if (is_array($valueVent)) {
    $mode = $valueVent['mode'];
    $mode = $valueVent['mode'] == 'auto'?'авто':'ручной';
    $colorMode = $valueVent['mode'] == 'auto'?'#00ff00':'#336699';
    $colorFan = '#003200';
    $workTime = '';
    if ($valueVent['run']) {
        $colorFan = '#00ff00';
        $workTime = date('G:i:s' , (int)$valueVent['time']);
    }

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

    $infoTemp = 't:'.$tempOut.'&deg tv:'.$tempVent.'&deg d:'.$deltaTemp.'&deg dE:'.$deltaETemp.'&deg dD:'.$deltaDTemp.'&deg';
    $infoHum = 'h:'.$humOut.'% hv:'.$humVent.'% d:'.$deltaHum.'% dE:'.$deltaEHum.'% dD:'.$deltaDHum.'%';
    $infoRun = 'Enable: '.$deltaEnable.' Disable:'.$deltaDisable;

    echo '<div id="kitchen_hood_mode" style="margin-left:5px;margin-top:2px">';
    echo '    <input id="kitchen_hood_last_status" value='.$dateLastStatus.' type="hidden"/>';
    echo '    <div>';
    echo '        <p>режим: <span style="color: '.$colorMode.'">'.$mode.'</span></p>';
    echo '    </div>';
    echo '    <div style="margin-top: 5px; display: flex">';
    echo '        <div style="width: 74px">';
    echo '            <div style="display: flex">';
    echo '                <div style="margin: 2px; width: 15px; height: 7px; background-color: '.$colorFan.'"></div>';
    echo '                <div style="margin-left: 5px"><img src="img2/icon_big/fan.png" alt=""></div>';
    echo '            </div>';
    echo '            <div style="float: right; margin-top: 5px"><p>'.$workTime.'</p></div>';
    echo '        </div>';
    echo '        <div style="margin-left: 20px; margin-top: 5px"><img src="img2/light_'.$valueLight.'.png" alt=""></div>';
    echo '        <div style="margin-left: 5px">';
    echo '            <div style="font-size: 70%">'.$infoTemp.'</div>';
    echo '            <div style="font-size: 70%">'.$infoHum.'</div>';
    echo '            <div style="font-size: 70%">'.$infoRun.'</div>';
    echo '        </div>';
    echo '    </div>';
    echo '</div>';
} else {
    echo '';
}