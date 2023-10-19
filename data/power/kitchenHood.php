<?php
$unit = managerUnits::getUnitLabel('kitchen_hood');
$valueVent = null;
$dateLastStatus = '';
if (!is_null($unit)) {
    $dataVent = $unit->getData();
    $valueVent = $dataVent->value;
    $dateLastStatus = $dataVent->date;
}

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

if (is_object($valueVent)) {
    $mode = $valueVent->mode == 'auto' ? 'авто' : 'ручной';
    $colorMode = $valueVent->mode == 'auto'?'#00ff00':'#336699';
    $colorFan = '#003200';
    if ($valueVent->run) $colorFan = '#00ff00';

    echo '<div id="kitchen_hood_mode" style="margin-left:5px">';
    echo '    <input id="kitchen_hood_last_status" value='.$dateLastStatus.' type="hidden"/>';
    echo '    <div>';
    echo '        <p>режим: <span style="color: '.$colorMode.'">'.$mode.'</span></p>';
    echo '    </div>';
    echo '    <div style="margin-top: 10px; display: flex">';
    echo '        <div style="width: 74px">';
    echo '            <div style="display: flex">';
    echo '                <div style="margin: 2px; width: 15px; height: 7px; background-color: '.$colorFan.'"></div>';
    echo '                <div style="margin-left: 5px"><img src="img2/icon_big/fan.png" alt=""></div>';
    echo '            </div>';
    echo '        </div>';
    echo '        <div style="margin-left: 15px; margin-top: 5px">';
    echo '          <img id="power_kitchen_hood_light" value="'.$valueLight.'" src="img2/light_'.$valueLight.'.png" alt="">';
    echo '        </div>';
    echo '    </div>';
    echo '</div>';
} else {
    echo '';
}