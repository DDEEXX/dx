<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

if ($_REQUEST['dev'] == 'temp') { //получаем температуру

    $label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";
    $unit = managerUnits::getUnitLabel($label);

    $temperatureClass = 'unActualDataSensor';
    $temperature = '--';

    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        /*        echo '--'; //пока так
                exit(); //тут надо подумать что возвращать*/
    } else {

        $classPlus = 'temperature_weather_plus';
        $classMinus = 'temperature_weather_minus';

        if (isset($_GET['color']) && $_GET['color'] == 'plan') {
            $classPlus = 'temperature_weather_plus_plan';
            $classMinus = 'temperature_weather_minus_plan';
        }

        $value = $unit->readValue();
        if (!is_null($value)) {
            $temperaturePrecision = DB::getConst('TemperaturePrecision');
            $temperature = (double)$value['Value'];
            $temperature = round($temperature, $temperaturePrecision);
            // время с последнего измерения в течение которого температура считается еще актуальной
            $actualTimeTemperature = DB::getConst('ActualTimeTemperature');
            $actualTemp = ((time() - strtotime($value['Date'])) < $actualTimeTemperature);
            if ($actualTemp) {
                $temperatureClass = $temperature < 0 ? $classMinus : $classPlus;
            }
        }
    }

    echo
    '<div class="' . $temperatureClass . '">'.
    $temperature.
    '</div>';

    unset($unit);
}

if ($_REQUEST['dev'] == 'temp_delta') { //получаем температуру

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
        $value1 = $unit1->readValue();
        $temp1 = $value1['Value'];
        $value2 = $unit2->readValue();
        $temp2 = $value2['Value'];

        if (is_numeric($temp1) && is_numeric($temp2)) {
            $temperaturePrecision = DB::getConst('TemperaturePrecision');
            $delta = round((double)$temp1 - (double)$temp2, $temperaturePrecision);
        }
    }

    echo '<div>'.$delta.'</div>';

    unset($unit1);
    unset($unit2);
}

if ($_REQUEST['dev'] == 'pressure') { //получаем атмосферное давление

    $label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";
    $unit = managerUnits::getUnitLabel($label);

    $pressure = '--';
    $actualPressureClass = 'unActualDataSensor';

    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        /*        echo '--'; //пока так
                exit(); //тут надо подумать что возвращать*/
    } else {
        $value = $unit->readValue();
        if (!is_null($value)) {
            $pressure = (double)$value['Value'];
            // время с последнего измерения в течение которого давление считается еще актуальной
            $actualTimePressure = DB::getConst('ActualTimePressure');
            $actualPressure = ((time() - strtotime($value['Date'])) < $actualTimePressure);
            $actualPressureClass = $actualPressure ? 'actualPressure' : 'unActualDataSensor';
            $pressure = round($pressure);
        }
    }

    echo '<div class="' . $actualPressureClass . '">
            ' . $pressure . '
          </div>';

    unset($unit);
}

if ($_REQUEST['dev'] == 'humidity') { //получаем влажность

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
        $value = $unit->readValue();
        if (!is_null($value)) {
            $humidity = (double)$value['Value'];
            // время с последнего измерения в течение которого влажность считается еще актуальной
            $actualTimeHumidity = DB::getConst('ActualTimePressure'); //совпадает с давлением
            $actualHumidity = ((time() - strtotime($value['Date'])) < $actualTimeHumidity);
            $actualHumidityClass = $actualHumidity ? 'actualHumidity' : 'unActualDataSensor';
            $humidity = round($humidity);
        }
    }

    echo '<div class="' . $actualHumidityClass . '">
            ' . $humidity . '
          </div>';

    unset($unit);
}

if ($_REQUEST['dev'] == 'wind') { //получаем влажность

    $wind = '--';
    $actualHumidityClass = 'unActualDataSensor';

    echo '<div class="' . $actualHumidityClass . '">
            ' . $wind . '
          </div>';

}

if ($_REQUEST['dev'] == 'light') { //получаем значение освещения

    $label = $_GET['label'];

    $unit = managerUnits::getUnitLabel($label);

    $keyStatus = 'off';

    if (!is_null($unit)) {
        $isLight = $unit->getValue();
        $keyStatus = $isLight ? 'on' : 'off';
    } else {
        $keyStatus = 'empty';
    }

    $place = explode(';', $_GET['place']);

    $nameImgFile = isset($_GET['img']) ? $_GET['img'] : 'light';

    if ($keyStatus == 'on') {
        $nameImgFile = 'img2/' . $nameImgFile . '_on.png';
    } else {
        $nameImgFile = 'img2/' . $nameImgFile . '_off.png';
    }

    echo '<div class="lamp light_status_' . $keyStatus . '" label="' . $label . '" style="top:' . $place[0] . 'px;left:' . $place[1] . 'px">';
    echo '<div class="lamp_img" style="top:5px;left:10px">';
    echo '<img class="' . $keyStatus . '_light" src="' . $nameImgFile . '">';
    echo '</div>';
    echo '</div>';
}

if ($_REQUEST['dev'] == 'cam') { //камеры

    $Monitor = $_GET['monitor'];

    echo '<img img id="monitor1" style="margin-top:5px;height:225px;width:400px" src="cam2\Monitor' . $Monitor . '.jpg">';

}
