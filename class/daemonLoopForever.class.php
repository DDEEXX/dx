<?php

require_once(dirname(__FILE__) . "/daemon.class.php");
require_once(dirname(__FILE__) . "/managerUnits.class.php");
require_once(dirname(__FILE__) . "/sharedMemory.class.php");
require_once(dirname(__FILE__) . "/logger.class.php");

class daemonLoopForever extends daemon
{
    const NAME_PID_FILE = 'loopForever.pid';
    const INTERVAL = 600; //Интервал обновления списка модулей (количество итераций)
    protected $stop_server = FALSE;

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run()
    {

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

                //Обходим все модули и обновляем их состояние. Если есть в массиве, то значение 1, если нет - 0
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

            usleep(200000); //ждем 0.2 секунду
            $i++;
            if ($i >= self::INTERVAL) {
                $listUnit1WireLoop = managerUnits::getListUnits1WireLoop(0);
                $i = 0;
            }
        }
    }
}