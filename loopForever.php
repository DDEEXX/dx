<?php
/**
 * Постоянный опрос датчиков, которые сами отправляют свое состояние.
 * Например, датчики 1-wire опрашиваемые по команде Read Conditional Search ROM
 * Created by PhpStorm.
 */

sleep(6);

//Создаем дочерний процесс весь код после pcntl_fork() будет выполняться двумя процессами: родительским и дочерним
$child_pid = pcntl_fork();
if ($child_pid) { // Выходим из родительского, привязанного к консоли, процесса
    exit();
}
// Делаем основным процессом дочерний.
posix_setsid();
// Дальнейший код выполнится только дочерним процессом, который уже отвязан от консоли

$fileDir = dirname(__FILE__);

require($fileDir . '/class/daemon.class.php');
require($fileDir . '/class/managerDevices.class.php');

ini_set('error_log', $fileDir . '/logs/errorLoopForever.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir . '/logs/application.log', 'ab');
$STDERR = fopen($fileDir . '/logs/daemonLoopForever.log', 'ab');

class daemonLoopForever extends daemon
{
    const NAME_PID_FILE = 'loopForever.pid';
    const UPDATE_UNITE_DELAY = 60; //Интервал обновления списка модулей, в секундах
    const PAUSE = 100000; //Пауза в основном цикле, в микросекундах (0.1 сек)
    const SEPARATOR_OWNET_ALARM_FILES = ',';

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    private function getAlarmOWireSensorDevice()
    {
        $listDevices = [];
        $sel = new selectOption();
        $sel->set('Disabled', 0);
        $sel->set('NetTypeID', netDevice::ONE_WIRE);
        $sel->set('DeviceTypeID', typeDevice::KEY_IN);
        $sel->set('OW_is_alarm', 1);
        $listDeviceSensor1Wire = managerDevices::getListDevices($sel);
        foreach ($listDeviceSensor1Wire as $device) {
            $devicePhysic = $device->getDevicePhysic();
            if (is_a($devicePhysic, 'iDeviceSensorPhysicOWire')) {
                $listDevices[$device->getDeviceID()] = $devicePhysic->getAddress();
            }
        }
        return $listDevices;
    }

    public function run()
    {
        parent::run();

        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $ow = new OWNet($OWNetAddress);

        $previousTime = time();

        $listDevice1Wire = $this->getAlarmOWireSensorDevice();

        while (!$this->stopServer()) {

            $alarmDir = '';
            $alarmDirData = $ow->dir('/alarm');
            if (is_array($alarmDirData) && array_key_exists('data', $alarmDirData)) {
                $alarmDir = $alarmDirData['data'];
            }

            $alarms = [];
            //если в alarm есть данные, то последний символ в строке - точка, если ничего нет, то пустая строка
            if (strlen($alarmDir)) { //удаляем последний символ - точку
                $alarmDir = substr($alarmDir, 0, -1);
                $listAlarmAddress = explode(self::SEPARATOR_OWNET_ALARM_FILES, $alarmDir);
                foreach ($listAlarmAddress as $fullAddress) {
                    $listAddress = explode('/', $fullAddress);
                    $address = array_pop($listAddress);
                    $alarms[$address] = true;
                }
            }

            $now = time();
            //Обходим все модули и обновляем их состояние. Если есть в массиве, то значение 1, если нет - 0
            foreach ($listDevice1Wire as $deviceID => $address) {
                if (array_key_exists($address, $alarms)) {
                    $value = 1;
                } else {
                    $value = 0;
                }
                $deviceData = new deviceData($deviceID);
                $deviceData->updateData($value, $now, false);
            }


            usleep(self::PAUSE); //ждем

            //обновляем список модулей через определенный промежуток времени
            if ($now - $previousTime > self::UPDATE_UNITE_DELAY) {
                $previousTime = $now;
                $listDevice1Wire = $this->getAlarmOWireSensorDevice();
            }

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов

        }
    }
}

$daemon = new daemonLoopForever($fileDir . '/tmp');
$daemonActive = $daemon->isDaemonActive();
if ($daemonActive == 0) {
    $daemon->run();
} else {
    logger::writeLog('Невозможно запустить демона daemonLoopForever, код возврата - ' . $daemonActive,
        loggerTypeMessage::ERROR, loggerName::ERROR);
}
