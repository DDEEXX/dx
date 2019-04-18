<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

if ($_REQUEST['dev'] == "temp") { //получаем температру

    $label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";

    $unit = managerUnits::getUnitLabel($label);

    if (is_null($unit)) {
        logger::writeLog('Молуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        exit(); //тут надо подумать что возвращать
    }

    $value = $unit->readValue();
    $temperaturePrecision = DB::getConst('TemperaturePrecision');
    $temperature = (double)$value['Value'];
    // время с последнего имерения в течение которого температура считается еще актуальной
    $actualTimeTemperature = DB::getConst('ActualTimeTemperature');
    $actualTemp = ((time() - strtotime($value['Date'])) < $actualTimeTemperature);
    $temperature = round($temperature, $temperaturePrecision);

    if (!$actualTemp) {
        echo '<style>
                #' . $label . ' {color: #8a8a8a}
              </style>';
    }

    echo "$temperature" . "&deg";

    unset($unit);
}

//if ( $_REQUEST['dev'] == "label" ) { //получаем значение цифрового датчика типа "сухой контакт"
//
//
//	$id = $d->getId($_GET['label']);
//	if (!$id) {
//		// такой записи нет в таблице tunits, надо что-то записать в лог, сделаю позже
//		echo "<img src='img2/icon/garage_err.png'>";
//	}
//
//	$nameTabValue = $d->getTabValue($id);
//	if (!$d->checkTab($nameTabValue)) {
//		// в БД нет таблицы и именем $nameTabValue, надо что-то записать в лог, сделаю позже
//		echo "<img src='img2/icon/garage_err.png'>";
//	}
//
//	if ($_GET['type']=='last'){
//		//$d->writeLog("label_last_".$id."_".$nameTabValue);
//		$Value = $d->getLastValue($id, $nameTabValue);
//
//		if ( $_GET['label'] == 'label_garage_door') {
//			if ($Value == null) { echo "<img src='img2/icon/garage_err.png'>"; } // в БД нет данных
//			elseif ($Value > 0) { echo "<img src='img2/icon/garage_close.png'>"; }
//			else { echo "<img src='img2/icon/garage_open.png'>"; }
//		}
//
//	}
//
//}
//
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
