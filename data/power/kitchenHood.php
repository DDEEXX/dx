<?php
require_once(dirname(__FILE__) . '/../../class/mqtt.class.php');
require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

if ($_REQUEST['dev'] == 'kitchenHood') {
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
        $colorMode = $valueVent->mode == 'auto' ? '#00ff00' : '#336699';
        $colorFan = '#003200';
        if ($valueVent->run) $colorFan = '#00ff00';

        echo '<div id="kitchen_hood_mode" style="margin-left:5px">';
        echo '    <input id="kitchen_hood_last_status" value=' . $dateLastStatus . ' type="hidden"/>';
        echo '    <div>';
        echo '        <p>режим: <span style="color: ' . $colorMode . '">' . $mode . '</span></p>';
        echo '    </div>';
        echo '    <div style="margin-top: 10px; display: flex">';
        echo '        <div style="width: 74px">';
        echo '            <div style="display: flex">';
        echo '                <div style="margin: 2px; width: 15px; height: 7px; background-color: ' . $colorFan . '"></div>';
        echo '                <div style="margin-left: 5px"><img src="img2/icon_big/fan.png" alt=""></div>';
        echo '            </div>';
        echo '        </div>';
        echo '        <div style="margin-left: 15px; margin-top: 5px">';
        echo '          <img id="power_kitchen_hood_light" value="' . $valueLight . '" src="img2/light_' . $valueLight . '.png" alt="">';
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
    } else {
        echo '';
    }
}
elseif ($_REQUEST['dev'] == 'check_ventStatus') {
    $result = ['update' => false];
    $dateStatus = (int)$_REQUEST['dateStatus'];
    $unit = managerUnits::getUnitLabel('kitchen_hood');
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
elseif ($_REQUEST['dev'] == 'info') {

    $unit = managerUnits::getUnitLabel('kitchen_hood');
    $valueVent = null;
    $dateLastStatus = '';
    if (!is_null($unit)) {
        $dataVent = $unit->getData();
        $valueVent = $dataVent->value;
        $dateLastStatus = $dataVent->date;
    }

    if (is_object($valueVent)) {

        $tempOut = $valueVent->tempOut;
        $humOut = $valueVent->humOut;
        $tempVent = $valueVent->tempVent;
        $humVent = $valueVent->humVent;
        $deltaEnable = $valueVent->deltaEnable;
        $deltaETemp = $valueVent->deltaTemperatureEnable;
        $deltaEHum = $valueVent->deltaHumidityEnable;
        $deltaDisable = $valueVent->deltaDisable;
        $deltaDTemp = $valueVent->deltaTemperatureDisable;
        $deltaDHum = $valueVent->deltaHumidityDisable;
        $deltaTemp = round($tempVent - $tempOut, 1);
        $deltaHum = round($humVent - $humOut, 1);

        $infoRun = 'Enable: ' . $deltaEnable . ' Disable:' . $deltaDisable;
        $colorEnable = '#003200';
        if ($deltaEnable) {
            $colorEnable = '#00ff00';
        }
        $colorDisable = '#003200';
        if ($deltaDisable) {
            $colorDisable = '#00ff00';
        }

        echo '<script src="js2/powerKitchenHood.js?version = 1.1"></script>';
        echo '<div style="display: flex; justify-content: space-between">';
        echo '  <div id="power_kitchen_hood_update_info" style="background: url(\'img2/icon_medium/refresh.png\') no-repeat center center; width: 1em"></div>';
        echo '  <div style="width: 80%">';
        echo '    <div style="display: flex; justify-content: flex-start">';
        echo '        <p style="width: 29%">Температура</p>';
        echo '        <p style="width: 24%">вытяжка ' . $tempVent . '&deg</p>';
        echo '        <p style="width: 24%">кухня ' . $tempOut . '&deg</p>';
        echo '        <p style="width: 24%">дельта ' . $deltaTemp . '&deg</p>';
        echo '    </div>';
        echo '    <div style="display: flex; justify-content: flex-start">';
        echo '        <p style="width: 29%">Влажность</p>';
        echo '        <p style="width: 24%">вытяжка ' . $humVent . '%</p>';
        echo '        <p style="width: 24%">кухня ' . $humOut . '%</p>';
        echo '        <p style="width: 24%">дельта ' . $deltaHum . '%</p>';
        echo '    </div>';
        echo '  </div>';
        echo '  <div>';
        echo '                    <div style="display: flex">';
        echo '                      <div style="margin: 2px; width: 15px; height: 7px; background-color: ' . $colorEnable . '"></div>';
        echo '                      <div style="margin-left: 5px">вкл</div>';
        echo '                    </div>';
        echo '                    <div style="display: flex">';
        echo '                      <div style="margin: 2px; width: 15px; height: 7px; background-color: ' . $colorDisable . '"></div>';
        echo '                      <div style="margin-left: 5px">выкл</div>';
        echo '                    </div>';
        echo '  </div>';
        echo '</div>';

        $arData = [
            0 => ['label'=>'Температура включения:', 'value'=>$deltaETemp, 'postfix'=>'&deg', 'property'=>'delta_temperature_enable'],
            1 => ['label'=>'Температура выключения:', 'value'=>$deltaDTemp, 'postfix'=>'&deg', 'property'=>'delta_temperature_disable'],
            2 => ['label'=>'Влажность включения:', 'value'=>$deltaEHum, 'postfix'=>'%', 'property'=>'delta_humidity_disable'],
            3 => ['label'=>'Влажность выключения:', 'value'=>$deltaDHum, 'postfix'=>'%', 'property'=>'delta_humidity_disable'],
        ];

        foreach ($arData as $data) {
            echo '    <div class="kh_setup_row" style="padding-top: 5px">';
            echo '        <div class="kh_setup_col1">';
            echo '          <span>' . $data['label'] . '</span> ';
            echo '          <span>' . $data['label'] . $data['postfix'] .'&deg</span>';
            echo '        </div>';
            echo '        <div class="kh_setup_col2"">';
            echo sprintf( "<input id=\"kh_%s\" class=\"property_spinner\" name=\"value\" value=\"%s\">",
                    $data['property'], $data['value']);
            echo '        </div>';
            echo '        <div class="kh_setup_col3">';
            echo sprintf("<button class=\"btn_kitchen_hood_set\" property=\"%s\" value=\"kh_%s\">установить</button>",
                $data['property'], $data['property']);
            echo '        </div>';
            echo '    </div>';
        }
    } else {
        echo '';
    }
}
elseif ($_REQUEST['dev'] == 'setProperties') {
    $property = $_POST['property'];
    $value = $_POST['value'];
    if (!is_numeric($value)) {
        exit;
    }
    $unit = managerUnits::getUnitLabel('kitchen_hood');
    if (is_null($unit)) exit;

    $device = $unit->getDevice();
    if (is_null($device)) exit;

    $devicePhysic = $device->getDevicePhysic();
    if (is_null($devicePhysic)) exit;

    $topic = $devicePhysic->getTopicSet();

    $value = (int)$value;
    $payload = json_encode([$property => $value]);

    $mqtt = mqttSend::connect();
    $mqtt->publish($topic, $payload);
}
elseif ($_REQUEST['dev'] == 'update') {
    $unit = managerUnits::getUnitLabel('kitchen_hood');
    if (!is_null($unit)) {
        $device = $unit->getDevice();
        if (!is_null($device)) {
            $device->requestData();
        }
    }
}