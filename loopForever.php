<?php
/**
 * Постоянный опрос датчиков, которые сами отправляют свое состояние.
 * Например, датчики 1-wire опрашиваемые по команде Read Conditional Search ROM
 * Created by PhpStorm.
 */

sleep(5);

//Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
$child_pid = pcntl_fork();
if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем основным процессом дочерний.
posix_setsid();
// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли

$fileDir = dirname(__FILE__);

require($fileDir."/class/daemon.class.php");
require($fileDir."/class/managerUnits.class.php");
require_once($fileDir."/class/logger.class.php");

ini_set('error_log',$fileDir.'/logs/errorLoopForever.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir.'/logs/application.log', 'ab');
$STDERR = fopen($fileDir.'/logs/daemonLoopForever.log', 'ab');

class daemonLoopForever extends daemon
{
    const NAME_PID_FILE = 'loopForever.pid';
    const UPDATE_UNITE_DELAY = 60; //Интервал обновления списка модулей, в секундах
    const PAUSE = 100000; //Пауза в основном цикле, в микросекундах (0.1 сек)

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run()
    {
        $this->putPitFile(); // устанавливаем PID файла

        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $ow = new OWNet($OWNetAddress);

        $previousTime = time();
        $listUnit1WireLoop = managerUnits::getListUnits1WireLoop(0);
        while (!$this->stopServer()) {

            $alarmDir = '';
            try {
                $alarmDirData = $ow->dir("/alarm");
                if (is_array($alarmDirData) && array_key_exists('data', $alarmDirData)) {
                    $alarmDir = $alarmDirData['data'];
                }
            }
            catch (Exception $e) {
            }

            $alarms = array();

            //если в alarm есть данные, то последний символ в строке - точка, если ничего нет, то пустая строка
            if (strlen($alarmDir)) { //удаляем последний символ - точку
                $alarmDir = substr($alarmDir, 0, -1);
                $listAlarmAddress = explode(',', $alarmDir);
                foreach ($listAlarmAddress as $fullAddress) {
                    $listAddress = explode('/', $fullAddress);
                    $address = array_pop($listAddress);
                    $alarms[$address] = true;
                }

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

            usleep(self::PAUSE); //ждем

            //обновляем список модулей через определенный промежуток времени
            $now = time();
            if ($now-$previousTime > self::UPDATE_UNITE_DELAY) {
                $previousTime = $now;
                $listUnit1WireLoop = managerUnits::getListUnits1WireLoop(0);
            }

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов

        }
    }
}

$daemon = new daemonLoopForever( $fileDir.'/tmp');
if ($daemon->isDaemonActive()) {
    exit();
}
$daemon->run();
