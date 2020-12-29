<?php
require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/lists.class.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');

//managerUnits::initUnits();

$OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);
$alarmDir = $OWNetDir.'/uncached/alarm';

$debug = true;
$debug_time = 0;

$listUnit1WireLoop = managerUnits::getListUnits1WireLoop(0);
$i = 0;
for ($q=0;$q<=100;$q++) {

    if (is_dir($alarmDir)) {
        $alarms = array();
        //Помещаем адреса всех сработавших модулей в массив
        if ($handle = opendir($alarmDir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $alarms[$file] = true;
                }
            }
            rewinddir($handle);
        }

        //Обходим все модули и обновляем их состояние. Если есть в массиве то значение 1, если нет - 0
        foreach ($listUnit1WireLoop as $uniteID => $address) {
            if (array_key_exists($address, $alarms)) {
                $value = 1;
            }
            else {
                $value = 0;
            }
            $unit = managerUnits::getUnitID($uniteID);
            $unit->updateValueLoop($value); //Обновляем данные в объекте модуля
            $unit->updateUnitSharedMemory();

            if ($debug) {
                if ($debug_time == 0) {
                    $unit_debug = managerUnits::getUnitID($uniteID);
                    $moveData = json_decode($unit_debug->getValues(), true);
                    $isMove = $moveData['value'];
                    $timeNoMove = $moveData['dataValue'];
                    logger::writeLog('move - '.$isMove.' data - '.$timeNoMove, loggerTypeMessage::NOTICE, loggerName::DEBUG);
                    var_dump($moveData);
                }
                $debug_time++;
                if ($debug_time == 10) {
                    $debug_time = 0;
                }
            }

        }

    }

    usleep(100000); //ждем 0.1 секунду

}
exit();



//$wiredir = "/mnt/1wire/uncached/";
//$alarmdir = $wiredir . "alarm";
//$key = "12.68441B000000";
//$i = 0;
//while ($i<=100) {
//    usleep(100000);
//    if ($handle = opendir($alarmdir)) {
//        while (false !== ($file = readdir($handle))) {
//            if ($file != "." && $file != "..") {
//                if ($file == $key) {
//                    $f = file($wiredir . $file . "/sensed.A");
//                    echo $f[0];
//                    echo " ....ON... \n";
//                } else {
//                    echo " off \n";
//                }
//            }
//        }
//        rewinddir($handle);
//    }
//    $i++;
//}
//closedir($handle);
