<?php

require_once(dirname(__FILE__) . "/class/managerUnits.class.php");
require_once(dirname(__FILE__) . "/class/sharedMemory.class.php");
require_once(dirname(__FILE__) . "/class/logger.class.php");

$unit = managerUnits::getUnitID("5");

echo $unit->


$OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);
$alarmDir = $OWNetDir.'/uncached/alarm';

$listUnit1WireLoop = managerUnits::getListUnits1WireLoop(0);
$i = 0;
while (true) {
    if ($this->stop_server) {
        break;
    }

    $alarms = array();
    if (is_dir($alarmDir)) {
        //Помещаем адреса всех сработавших модулей в массив
        try {
            if ($handle = opendir($alarmDir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $alarms[$file] = true;
                    }
                }
                rewinddir($handle);
            }
        }
        catch (Exception $e) {
            logger::writeLog($e->getMessage(), loggerTypeMessage::ERROR, loggerName::DEBUG);
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

        }

    }

    usleep(100000); //ждем 0.1 секунду
    $i++;
    if ($i >= self::INTERVAL) {
        $listUnit1WireLoop = managerUnits::getListUnits1WireLoop(0);
        $i = 0;
    }
}
