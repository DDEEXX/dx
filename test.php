<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:49
 */

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

managerUnits::initUnits();

$temperatureUnits = managerUnits::getListUnits(typeUnit::TEMPERATURE, 0);
foreach ($temperatureUnits as $unit) {
    if (is_null($unit)) continue;
    //опрашиваем датчики, которые могут вернуть значение в любое время
    if ($unit->getModeDeviceValue() == modeDeviceValue::GET_VALUE) {
        $result = $unit->updateValue(); //Считываем и обновляем данные в объекте модуля
        if (!is_null($result)) {
            $unit->writeCurrentValueDB(); //Записываем данные в базу данных
        }
    }
}



//require_once(dirname(__FILE__) . '/class/mqqt.class.php');
//
//echo 'S1:'.date("H:i:s").PHP_EOL;

//managerUnits::initUnits();

//$unitsID = managerUnits::getUnitStatusTopic('home/bathroom/mirror_light/stat/POWER');
//foreach ($unitsID as $id) {
//    $unite = managerUnits::getUnitID($id);
//    if (is_null($unite)) {
//        continue;
//    }
//}

//$unit = managerUnits::getUnitLabel("bathroom_mirror_light");
//
//$unit->setValue('ON', statusKey::OUTSIDE);
//sleep(1);
//$unit->setValue('OFF', statusKey::OUTSIDE);

//$mqqt = mqqt::Connect(true);
//for ($i=0;$i<100;$i++) {
//    $mqqt->loop();
//    usleep(100000);
//    //echo 'B'.$i.":".date("H:i:s").PHP_EOL;
//}
//echo "FINISH".date("H:i:s").PHP_EOL;