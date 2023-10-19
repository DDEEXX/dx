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
        echo '<div style="margin-top: 20px">';
        echo '    <div class="kh_setup_row">';
        echo '        <div class="kh_setup_col1">';
        echo '          <span>Температура включения:</span> ';
        echo '          <span>' . $deltaETemp . '&deg</span>';
        echo '        </div>';
        echo '        <div class="kh_setup_col2">';
        echo '          <input id="kh_deltaETemp" name="value" value="' . $deltaETemp . '">';
        echo '        </div>';
        echo '        <div class="kh_setup_col3">';
        echo '            <button id="btn_deltaETemp" class="btn_kitchen_hood_set" property="delta_temperature_enable" value="kh_deltaETemp">установить</button>';
        echo '        </div>';
        echo '    </div>';
        echo '    <div class="kh_setup_row" style="padding-top: 5px">';
        echo '        <div class="kh_setup_col1">';
        echo '          <span>Температура выключения:</span> ';
        echo '          <span>' . $deltaDTemp . '&deg</span>';
        echo '        </div>';
        echo '        <div class="kh_setup_col2"">';
        echo '          <input id="kh_deltaDTemp" name="value" value="' . $deltaDTemp . '">';
        echo '        </div>';
        echo '        <div class="kh_setup_col3">';
        echo '            <button id="btn_deltaDTemp" class="btn_kitchen_hood_set" property="delta_temperature_disable" value="kh_deltaDTemp">установить</button>';
        echo '        </div>';
        echo '    </div>';
        echo '    <div class="kh_setup_row" style="padding-top: 5px">';
        echo '        <div class="kh_setup_col1">';
        echo '          <span>Влажность включения:</span> ';
        echo '          <span>' . $deltaEHum . '%</span>';
        echo '        </div>';
        echo '        <div class="kh_setup_col2"">';
        echo '          <input id="kh_deltaEHum" name="value" value="' . $deltaEHum . '">';
        echo '        </div>';
        echo '        <div class="kh_setup_col3">';
        echo '            <button id="btn_deltaEHum" class="btn_kitchen_hood_set" property="delta_humidity_disable" value="kh_deltaEHum">установить</button>';
        echo '        </div>';
        echo '    </div>';
        echo '    <div class="kh_setup_row" style="padding-top: 5px">';
        echo '        <div class="kh_setup_col1">';
        echo '          <span>Влажность выключения:</span> ';
        echo '          <span>' . $deltaDHum . '%</span>';
        echo '        </div>';
        echo '        <div class="kh_setup_col2"">';
        echo '          <input id="kh_deltaDHum" name="value" value="' . $deltaDHum . '">';
        echo '        </div>';
        echo '        <div class="kh_setup_col3">';
        echo '            <button id="btn_deltaDHum" class="btn_kitchen_hood_set" property="delta_humidity_disable" value="kh_deltaDHum">установить</button>';
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
        echo '<div style="display: flex"></div>';
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