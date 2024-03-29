<?php

require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');

const statusKeyData = ['', 'движение', 'web', '', 'неизвестно', 'модуль', 'датчик', 'сенсор'];

//получить статус состояния по числовому коду в виде строки
function getTitleStatus($status) {
    if (array_key_exists($status, statusKeyData)) {
        return statusKeyData[$status];
    }
    return '';
}

if ($_REQUEST['dev'] == 'temp') { //получаем температуру

    $label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";

    $temperatureClass = 'unActualDataSensor';
    $temperature = '--';

    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
    } else {

        $classPlus = 'temperature_weather_plus';
        $classMinus = 'temperature_weather_minus';

        if (isset($_GET['color']) && $_GET['color'] == 'plan') {
            $classPlus = 'temperature_weather_plus_plan';
            $classMinus = 'temperature_weather_minus_plan';
        }

        $formatData = $unit->getData();
        $valueData = $formatData->getDataArray();
        if (!is_null($valueData)) {
            $valueNull = $valueData['valueNull'];
            if (!$valueNull) {
                $temperaturePrecision = DB::getConst('TemperaturePrecision');
                $temperature = (double)$valueData['value'];
                $temperature = round($temperature, $temperaturePrecision);
                // время с последнего измерения в течение которого температура считается еще актуальной
                $actualTimeTemperature = DB::getConst('ActualTimeTemperature');
                $actualTemp = (time() - $valueData['date']) < $actualTimeTemperature;
                if ($actualTemp) {
                    $temperatureClass = $temperature < 0 ? $classMinus : $classPlus;
                }
            }
        }
    }

    echo
    '<div class="' . $temperatureClass . '">'.
    $temperature.
    '</div>';

    unset($unit);
}

elseif ($_REQUEST['dev'] == 'temp_delta') { //получаем температуру

    $label1 = $_GET['label1']; //значение поля "UnitLabel" в таблице "tunits";
    $unit1 = managerUnits::getUnitLabel($label1);

    $label2 = $_GET['label2']; //значение поля "UnitLabel" в таблице "tunits";
    $unit2 = managerUnits::getUnitLabel($label2);

    $delta = '--';

    if (is_null($unit1)) {
        logger::writeLog('Модуль с именем :: ' . $label1 . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
    }
    elseif (is_null($unit2)) {
        logger::writeLog('Модуль с именем :: ' . $label2 . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
    }
    else {
        $formatData = $unit1->getData();
        $valueData = $formatData->getDataArray();
        $temp1 = $valueData['valueNull'] ? null : (double)$valueData['value'];

        $formatData = $unit2->getData();
        $valueData = $formatData->getDataArray();
        $temp2 = $valueData['valueNull'] ? null : (double)$valueData['value'];

        if (is_numeric($temp1) && is_numeric($temp2)) {
            $temperaturePrecision = DB::getConst('TemperaturePrecision');
            $delta = round((double)$temp1 - (double)$temp2, $temperaturePrecision);
        }
    }

    echo '<div>'.$delta.'</div>';

    unset($unit1);
    unset($unit2);
}

elseif ($_REQUEST['dev'] == 'pressure') { //получаем атмосферное давление

    $label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";
    $unit = managerUnits::getUnitLabel($label);

    $pressure = '--';
    $actualPressureClass = 'unActualDataSensor';

    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
    } else {
        $formatData = $unit->getData();
        $valueData = $formatData->getDataArray();
        if (!is_null($valueData)) {
            $valueNull = $valueData['valueNull'];
            if (!$valueNull) {
                $pressure = (double)$valueData['value'];
                // время с последнего измерения в течение которого давление считается еще актуальной
                $actualTimePressure = DB::getConst('ActualTimePressure');
                $actualPressure = (time() - $valueData['date']) < $actualTimePressure;
                $actualPressureClass = $actualPressure ? 'actualPressure' : 'unActualDataSensor';
                $pressure = round($pressure);
            }
        }
    }

    echo '<div class="' . $actualPressureClass . '">
            ' . $pressure . '
          </div>';

    unset($unit);
}

elseif ($_REQUEST['dev'] == 'humidity') { //получаем влажность

    $label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";
    $unit = managerUnits::getUnitLabel($label);

    $humidity = '--';
    $actualHumidityClass = 'unActualDataSensor';

    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        /*        echo '--'; //пока так
                exit(); //тут надо подумать что возвращать*/
    } else {
        $formatData = $unit->getData();
        $valueData = $formatData->getDataArray();
        if (!is_null($valueData)) {
            $valueNull = $valueData['valueNull'];
            if (!$valueNull) {
                $humidity = (double)$valueData['value'];
                // время с последнего измерения в течение которого влажность считается еще актуальной
                $actualTimeHumidity = DB::getConst('ActualTimePressure'); //совпадает с давлением
                $actualHumidity = (time() - $valueData['date']) < $actualTimeHumidity;
                $actualHumidityClass = $actualHumidity ? 'actualHumidity' : 'unActualDataSensor';
                $humidity = round($humidity);
            }
        }
    }

    echo '<div class="' . $actualHumidityClass . '">
            ' . $humidity . '
          </div>';

    unset($unit);
}

elseif ($_REQUEST['dev'] == 'wind') { //получаем влажность

    $wind = '--';
    $actualHumidityClass = 'unActualDataSensor';

    echo '<div class="' . $actualHumidityClass . '">
            ' . $wind . '
          </div>';

}

elseif ($_REQUEST['dev'] == 'light') { //получаем значение освещения

    $label = $_GET['label'];
    $unit = managerUnits::getUnitLabel($label);
    $labelSensor = '';
    $unitSensor = null;
    if (!empty($_GET['labelSensor'])) {
        $unitSensor = managerUnits::getUnitLabel($_GET['labelSensor']);
        $labelSensor = ' labelSensor = "'.$_GET['labelSensor'].'"';
    }
    $value = 'off';
    $status = 0;
    $payload = 'on';

    if (!is_null($unit)) {
        if (!is_null($unitSensor)) {
            $formatData = $unitSensor->getData();
            $valueData = $formatData->getDataArray();
        } else {
            $formatData = $unit->getData();
            if ($formatData instanceof iDeviceDataValue) {
                $valueData = $formatData->getDataArray();
            } else {
                if ($unit instanceof newYearGarlandUnit) {
                    $valueData = ['value'=>0, 'valueNull'=>true];
                    if (property_exists($formatData, 'value')) {
                        if (property_exists($formatData->value, 'state')) {
                            $valueData['valueNull'] = false;
                            $valueData['value'] = $formatData->value->state == 'on' ? 1 : 0;
                        }
                    }
                }
            }
        }
        if (!is_null($valueData)) {
            $valueNull = $valueData['valueNull'];
            if (!$valueNull) {
                $value = (int)$valueData['value'] > 0 ? 'on' : 'off';
                //определим действие для нажатия по текущему состоянию, на случай отсутствия фиксированного действия
                $payload = (int)$valueData['value'] > 0 ? 'off' : 'on';
            }
        }
        if (isset($_REQUEST['payload'])) { //действие по нажатию из конкретного значения
            $payload = $_REQUEST['payload'];
        }
    }

    $place = explode(';', $_GET['place']);
    //$style = 'display: flex;align-items:center;justify-content:center;top:' . $place[0] . 'px;left:' . $place[1] . 'px';
    $style = sprintf('display: flex;align-items:center;justify-content:center;top:%spx;left:%spx',
        $place[0], $place[1]);
    if (isset($_GET['size'])) {
        $size = explode(';', $_GET['size']);
        //$style = $style.';height: '.$size[0].'px;width: '.$size[1].'px';
        $style = $style . sprintf(';height:%spx;width:%spx', $size[0], $size[1]);
    }
    else
        $style = $style.';height: 40px;width: 40px';

    $nameImgFile = isset($_GET['img']) ? $_GET['img'] : 'light';
    if ($value == 'on') {
        $nameImgFile = 'img2/' . $nameImgFile . '_on.png';
    } else {
        $nameImgFile = 'img2/' . $nameImgFile . '_off.png';
    }

    $input = sprintf('<div class="light_plan_lamp_click light_plan_lamp_%s" label="%s" %s value="%s" payload="%s" style="%s">',
        $value, $label, $labelSensor, $value, $payload, $style);
    echo $input;

//    echo '<div class="light_plan_lamp_click light_plan_lamp_'.$value.'" label="'.$label.
//        '"'.$labelSensor.' value="'.$value.'" payload="'.$payload.' '.$style.'>';
    echo '<div class="light_plan_lamp_img">';
    echo '<img class="' . $value . '_light" src="' . $nameImgFile . '">';
    echo '</div>';
    echo '</div>';
}

elseif ($_REQUEST['dev'] == 'light_tile') {
    $label = $_GET['label'];
    $unit = managerUnits::getUnitLabel($label);
    $labelSensor = '';
    $unitSensor = null;
    if (!empty($_GET['labelSensor'])) {
        $unitSensor = managerUnits::getUnitLabel($_GET['labelSensor']);
        $labelSensor = ' labelSensor = "'.$_GET['labelSensor'].'"';
    }
    $value = 'off';
    $status = 0;
    $payload = 'on';
    if (!is_null($unit)) {
        if (!is_null($unitSensor)) {
            $formatData = $unitSensor->getData();
            $valueData = $formatData->getDataArray();
        } else {
            $formatData = $unit->getData();
            if ($formatData instanceof iDeviceDataValue) {
                $valueData = $formatData->getDataArray();
            }
            else {
                if ($unit instanceof newYearGarlandUnit) {
                    $valueData = ['value'=>0, 'valueNull'=>true];
                    if (property_exists($formatData, 'value')) {
                        if (property_exists($formatData->value, 'state')) {
                            $valueData['valueNull'] = false;
                            $valueData['value'] = $formatData->value->state == 'on' ? 1 : 0;
                        }
                    }
                }
            }
        }
        if (!is_null($valueData)) {
            $valueNull = $valueData['valueNull'];
            $status = $valueData['status'];
            if (!$valueNull) {
                $value = (int)$valueData['value'] > 0 ? 'on' : 'off';
                //определим действие для нажатия по текущему состоянию, на случай отсутствия фиксированного действия
                $payload = (int)$valueData['value'] > 0 ? 'off' : 'on';
            }
        }
        if (isset($_REQUEST['payload'])) { //действие по нажатию из конкретного значения
            $payload = $_REQUEST['payload'];
        }
    }

    echo '<div  style="display: flex; align-items:flex-end">';
    echo '    <div class="light_tile_lamp_'.$value.' light_tile_lamp_click" label="'.$label.'"'.$labelSensor.
        ' value="'.$value.'" payload="'.$payload.'"></div>';
    echo '    <div class="light_tile_lamp_status">'.getTitleStatus($status).'</div>';
    echo '</div>';

}

elseif ($_REQUEST['dev'] == 'test_status') {
    $result = ['red'=>false, 'yellow'=>false, 'green'=>false];
    $devicesTestCode = managerDevices::getLastTestCode();
    foreach ($devicesTestCode as $testCode) {
        switch ($testCode['Code']) {
            case testDeviceCode::WORKING :
            case testDeviceCode::NO_TEST :
            case testDeviceCode::IS_MQTT_DEVICE :
            case testDeviceCode::DISABLED :
                $result['green'] = true;
                break;
            case testDeviceCode::NO_DEVICE :
            case testDeviceCode::NO_VALUE :
            case testDeviceCode::ONE_WIRE_ALARM :
                $result['yellow'] = true;
                break;
            case testDeviceCode::NO_CONNECTION :
            case testDeviceCode::ONE_WIRE_ADDRESS :
                $result['red'] = true;
                break;
        }
    }
    header('Content-Type: application/json');
    echo json_encode($result);
}

elseif ($_REQUEST['dev'] == 'check_value') {
    $result = [];
    $labels = $_POST['labels'];
    foreach ($labels as $label) {
        $unit = managerUnits::getUnitLabel($label);
        if (!is_null($unit)) {
            $formatData = $unit->getData();
            if ($formatData instanceof iDeviceDataValue) {
                $valueData = $formatData->getDataArray();
            }
            else {
                if ($unit instanceof newYearGarlandUnit) {
                    $valueData = ['value'=>0, 'valueNull'=>true];
                    if (property_exists($formatData, 'value')) {
                        if (property_exists($formatData->value, 'state')) {
                            $valueData['valueNull'] = false;
                            $valueData['value'] = $formatData->value->state == 'on' ? 1 : 0;
                        }
                    }
                }
            }
            $value = -1;
            if (!is_null($valueData)) {
                $valueNull = $valueData['valueNull'];
                if (!$valueNull) {
                    $value = (int)$valueData['value'] > 0 ? 1 : 0;
                }
            }
            $result[] = ['label'=>$label, 'value'=>$value];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($result);
}


