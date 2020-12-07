<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

if ($_REQUEST['dev'] == "temp") { //получаем температру

    $label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";

    $unit = managerUnits::getUnitLabel($label);

    if (is_null($unit)) {
        logger::writeLog('Молуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        echo '--'; //пока так
        exit(); //тут надо подумать что возвращать
    }

    $classPlus  = 'temperature_weather_plus';
    $classMinus = 'temperature_weather_minus';

    if (isset($_GET['color'])) {
        if ($_GET['color'] == 'plan') {
            $classPlus  = 'temperature_weather_plus_plan';
            $classMinus = 'temperature_weather_minus_plan';
        }
    }

    $temperatureClass = 'unActualTemperature';
    $value = $unit->readValue();
    if (is_null($value)) {
        $temperature = '--';
    }
    else {
        $temperaturePrecision = DB::getConst('TemperaturePrecision');
        $temperature = (double)$value['Value'];
        $temperature = round($temperature, $temperaturePrecision);
        // время с последнего имерения в течение которого температура считается еще актуальной
        $actualTimeTemperature = DB::getConst('ActualTimeTemperature');
        $actualTemp = ((time() - strtotime($value['Date'])) < $actualTimeTemperature);
        if ($actualTemp) {
            $temperatureClass = $temperature<0?$classMinus:$classPlus;
        }
    }

    echo '<div class="' . $temperatureClass . '">
            ' . $temperature . ' &deg
          </div>';

    unset($unit);
}

if ($_REQUEST['dev'] == "pressure") { //получаем атмосферное давление

    $label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";

    $unit = managerUnits::getUnitLabel($label);

    if (is_null($unit)) {
        logger::writeLog('Молуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        echo '--'; //пока так
        exit(); //тут надо подумать что возвращать
    }

    $actualPressureClass = 'unActualPressure';
    $value = $unit->readValue();
    if (is_null($value)) {
        $pressure = '--';
    }
    else {
        $pressure = (double)$value['Value'];
        // время с последнего имерения в течение которого давление считается еще актуальной
        $actualTimePressure = DB::getConst('ActualTimePressure');
        $actualPressure = ((time() - strtotime($value['Date'])) < $actualTimePressure);
        $actualPressureClass = $actualPressure ? 'actualPressure' : 'unActualPressure';
        $pressure = round($pressure);
    }

    echo '<div class="' . $actualPressureClass . '">
            ' . $pressure . '
          </div>';

    unset($unit);
}

if ($_REQUEST['dev'] == "light") { //получаем значение освещения

    $label = $_GET['label'];

    $unit = managerUnits::getUnitLabel($label);

    $keyStatus = 'off';

    if (!is_null($unit)) {
        $isLight = $unit->getValue();
        $keyStatus = $isLight ? 'on' : 'off';
    }
    else {
        $keyStatus = 'empty';
    }

    $place = explode(";", $_GET['place']);

    $nameImgFile = isset($_GET['img']) ? $_GET['img'] : 'light';

    if ($keyStatus == 'on') {
        $nameImgFile = 'img2/' . $nameImgFile . '_on.png';
    }
    else {
        $nameImgFile = 'img2/' . $nameImgFile . '_off.png';
    }

    echo '<div class="lamp light_status_' . $keyStatus . '" label="' . $label . '" style="top:' . $place[0] . 'px;left:' . $place[1] . 'px">';
    echo '<div class="lamp_img" style="top:5px;left:10px">';
    echo '<img class="' . $keyStatus . '_light" src="' . $nameImgFile . '">';
    echo '</div>';
    echo '</div>';
}

if ($_REQUEST['dev'] == "cam") { //получаем значение освещения

    $Monitor = $_GET['monitor'];

    echo '<img img id="monitor1" style="margin-top:5px;height:225px;width:400px" src="cam2\Monitor' . $Monitor . '.jpg">';

}
