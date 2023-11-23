<?php
require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');

if ($_REQUEST['dev'] == 'gasSensor') {
    $label = $_GET['label'];
    $title = $_GET['title'];
    $unit = managerUnits::getUnitLabel($label);
    $value = null;
    $dateLastStatus = '';
    if (!is_null($unit)) {
        $data = $unit->getData();
        if (!is_null($data)) {
            $value = $data->value;
            $dateLastStatus = $data->date;
        }
    }

    $colorSensor = '#323200';
    $valueGas = '';
    if (is_object($value)) {
        $colorSensor = '#00ff00';
        if ($value->alarm) $colorSensor = '#ff0000';
        $valueGas = $value->gas;
    }

    echo '<div style="display: flex; margin-top: 10px">';
    echo '    <div style="width: 70px"><p>' . $title . '</p></div>';
    echo '    <div style="margin: 2px; width: 15px; height: 7px; background-color: ' . $colorSensor . '"></div>';
    echo '    <div style="margin-left: 5px">' . $valueGas . '</div>';
    echo '    <input id="' . $label . '_last_update" value=' . $dateLastStatus . ' type="hidden"/>';
    echo '</div>';
} elseif ($_REQUEST['dev'] == 'check_gasSensorStatus') {
    $result = ['update' => false];
    $dateStatus = (int)$_REQUEST['dateStatus'];
    $label = $_REQUEST['label'];
    $unit = managerUnits::getUnitLabel($label);
    if (!is_null($unit)) {
        $data = $unit->getData();
        $dateLastStatus = $data->date;
        if ($dateStatus != $dateLastStatus) {
            $result['update'] = true;
        }
    }
    header('Content-Type: application/json');
    echo json_encode($result);
} elseif ($_REQUEST['dev'] == 'dialogSetupContent') {

    $label = $_REQUEST['label'];
    $unit = managerUnits::getUnitLabel($label);
    $valueSensor = null;
    if (!is_null($unit)) {
        $dataSensor = $unit->getData();
        $valueSensor = $dataSensor->value;
        $dateLastSensor = $dataSensor->date;
    }

    if (is_object($valueSensor)) {

        $gas = $valueSensor->gas;
        $igas = $valueSensor->igas;
        $alarm = $valueSensor->alarm;
        $threshold = is_numeric($valueSensor->threshold) ?
            (int)$valueSensor->threshold : 0;
        $detectInterval = is_numeric($valueSensor->sensor_detect_interval) ?
            (int)$valueSensor->sensor_detect_interval : 0;
        $stateInterval = is_numeric($valueSensor->state_send_interval) ?
            (int)($valueSensor->state_send_interval / 1000) : 0;
        $alarmInterval = is_numeric($valueSensor->alarm_send_interval) ?
            (int)($valueSensor->alarm_send_interval / 1000) : 0;
        $availabilityInterval = is_numeric($valueSensor->send_availability_interval) ?
            (int)($valueSensor->send_availability_interval / 1000) : 0;

        $arData = [
            0 => ['label' => 'Текущее показание:', 'value' => $igas],
            1 => ['label' => 'Среднее показание:', 'value' => $gas],
            2 => ['label' => 'Тревога:', 'value' => $alarm],
        ];

        echo '<script src="js2/powerGasSensor.js?version = 1.1"></script>';
        echo '<div>';
        echo '  <div id="' . $label . '_update_info" style="background: url(\'img2/icon_medium/refresh.png\') no-repeat center center; width: 1em; height: 2em"></div>';
        echo '</div>';
        foreach ($arData as $data) {
            echo '    <div class="kh_setup_row" style="padding-top: 10px">';
            echo '        <div class="kh_setup_col1">';
            echo '          <span>' . $data['label'] . '</span> ';
            echo '          <span>' . $data['value'] . '</span>';
            echo '        </div>';
            echo '    </div>';
        }

        $arData = [
            0 => ['label' => 'Порог тревоги:', 'value' => $threshold, 'property' => 'threshold'],
            1 => ['label' => 'Интервал опроса для среднего (мс):', 'value' => $detectInterval, 'property' => 'detectInterval'],
            2 => ['label' => 'Отправлять состояние (сек):', 'value' => $stateInterval, 'property' => 'stateInterval'],
            3 => ['label' => 'Отправлять тревогу каждые (сек):', 'value' => $alarmInterval, 'property' => 'alarmInterval'],
            4 => ['label' => 'Отправлять активность (сек):', 'value' => $availabilityInterval, 'property' => 'availabilityInterval'],
        ];

        foreach ($arData as $data) {
            echo '    <div class="kh_setup_row" style="padding-top: 5px">';
            echo '        <div class="kh_setup_col1">';
            echo '          <span>' . $data['label'] . '</span> ';
            echo '          <span>' . $data['value'] . '</span>';
            echo '        </div>';
            echo '        <div class="kh_setup_col2">';
            echo sprintf("<input id=\"in_%s_%s\" class=\"property_spinner\" name=\"value\" value=\"%s\">",
                $label, $data['property'], $data['value']);
            echo '        </div>';
            echo '        <div class="kh_setup_col3">';
            echo sprintf("<button id=\"btn_%s_%s\" class=\"btn_gas_sensor_set\" property=\"%s\" label=\"%s\" value=\"in_%s_%s\">установить</button>",
                $label, $data['property'], $data['property'], $label, $label, $data['property']);
            echo '        </div>';
            echo '    </div>';
        }

    } else {
        echo '';
    }
} else if ($_REQUEST['dev'] == 'updateInfo') {
    $label = $_REQUEST['label'];
    $unit = managerUnits::getUnitLabel($label);
    if (!is_null($unit)) {
        $device = $unit->getDevice();
        if (!is_null($device)) {
            $device->requestData();
        }
    }
} else if ($_REQUEST['dev'] == 'set') {

    $arMap = [
        'threshold' => ['threshold', 1],
        'detectInterval' => ['sensor_detect_interval', 1],
        'stateInterval' => ['state_send_interval', 1000],
        'alarmInterval' => ['alarm_send_interval', 1000],
        'availabilityInterval' => ['send_availability_interval', 1000],
    ];

    $property = $_POST['property'];
    $value = $_POST['value'];
    $label = $_POST['label'];
    if (!is_numeric($value)) exit;
    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) exit;
    $device = $unit->getDevice();
    if (is_null($device)) exit;
    $devicePhysic = $device->getDevicePhysic();
    $topic = $devicePhysic->getTopicSet();
    if (!strlen($topic)) exit;

    $mapData = $arMap[$property];
    $value = (int)$value * $mapData[1];
    $payload = json_encode([$mapData[0] => $value]);

    $mqtt = mqttSend::connect();
    $mqtt->publish($topic, $payload);
}