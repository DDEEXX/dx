<?php

require_once(dirname(__FILE__).'/class/managerUnits.class.php');

if ( $_REQUEST['dev'] == "temp" ) { //получаем температру

	$label = $_GET['label']; //значение поля "UnitLabel" в таблице "tunits";

	$unit = managerUnits::getUnitLabel($label);

	if (!is_null($unit)) {
        $value = $unit->readValue();
        $temperaterePrecision = DB::getConst('TemperaterePrecision');
        $temperature = (double)$value['Value'];
        // время с последнего имерения в течение которого температура считается еще актуальной
        $actualTimeTemperature = DB::getConst('ActualTimeTemperature');
        $actualTemp = ( (time() - strtotime($value['Date'])) < $actualTimeTemperature );
        $temperature = round($temperature, $temperaterePrecision);
        echo "$temperature"."&deg";

        if ($actualTemp) {
//            echo '<style>
//                #'.$label.' {color: #8B4513}
//              </style>';
        }
        else {
            echo '<style>
                #'.$label.' {color: #8a8a8a}
              </style>';
        }

    }
    else {
        $log = logger::getLogger();
        $log->log('Молуль с именем :: '.$label.' :: не найден', logger::ERROR);
        unset($log);
        exit(); //тут надо подумать что возвращать
    }

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
//	if (!$d->chekTab($nameTabValue)) {
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
//if ( $_REQUEST['dev'] == "light" ) { //получаем значение освещения
//
//	$label = $_GET['label'];
//
//	$idUnit = $d->getId($label);
//
//	$keyStatus = 'off';
//
//	if (!empty($idUnit)) {
//
//		$unitInfo = $d->getUnitInfo($idUnit);
//
//		$net = $unitInfo['NetTitle'];
//
//		if ($net == '1-wire') {
//			$UnitModule = $unitInfo['UnitModule'];
//
//			if ($UnitModule == 'Key') { //напрямую через симистерный ключ
//				$ow=new OWNet("tcp://localhost:3000");
//				$keyStatus = $d->getKeyOut($_GET['label'], $ow);
//				unset($ow);
//			}
//			elseif ($UnitModule == 'RIO') { //через реле РИО
//				$keyStatus = $d->getValueKeyInLast($_GET['is_light']);
//			}
//			$keyStatus = $keyStatus?'on':'off';
//		}
//	}
//	else {
//		$d->writeLog('Не найден модуль с именем :: '.$label);
//		$keyStatus = 'empty';
//	}
//
//	$place = explode(";", $_GET['place']);
//
//	$nameImgFile = "";
//
//	if ($keyStatus == 'on') {
//		$nameImgFile = "light_on.png";
//	}
//	elseif ($keyStatus == 'off') {
//		$nameImgFile = "light_off.png";
//	}
//
//	echo "<div class='lamp ".$keyStatus."' label='".$label."' style='top:".$place[0]."px;left:".$place[1]."px'>";
//	echo "<div class='lamp_img' style='top:5px;left:10px'>";
//	echo "<img class='".$keyStatus."_l' src='img2/".$nameImgFile."'>";
//	echo "</div>";
//	echo "</div>";
//
//}

?>
