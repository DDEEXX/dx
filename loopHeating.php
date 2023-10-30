<?php
/**
 * Отопление
 */

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

ini_set('error_log', $fileDir . '/logs/errorLoopHeating.log');
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen($fileDir . '/logs/application.log', 'ab');
$STDERR = fopen($fileDir . '/logs/daemonLoopHeating.log', 'ab');

class daemonLoopHeating extends daemon
{
    const NAME_PID_FILE = 'loopHeating.pid';
    const PAUSE = 30; //Пауза в основном цикле, в секундах (30 сек)

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run()
    {
        parent::run();

        $unitBoiler = managerUnits::getUnitLabel('boiler_pid');
        $unitPID = managerUnits::getUnitLabel('boiler_pid');
        if (is_null()) return;

        $startTime = time();
        $nextStep = $startTime + self::PAUSE;
        $doStep = false;

        while (!$this->stopServer()) {

            $now = time();

            if ($now>$nextStep) {
                $nextStep = $startTime + (((int)(($now - $startTime)/self::PAUSE))+1) * self::PAUSE;
                $doStep = false;
            }

            if ($now < $nextStep && !$doStep) {
                //выполняем алгоритм управления отопление
                $optionsPID = $unitPID->getOptions();
                boiler($optionsPID);

                $doStep = true;
            }

            sleep(1); //ждем

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов
        }
    }

    function boiler($optionsPID)
    {

    }

    private function PID($tempTarget, $tempCurrent, $tempCurrentLast, &$iError, $dt, $KP, $KI, $KD, $opHigh, $opLow)
    {
        // calculate the $error
        $error = $tempTarget - $tempCurrent;
        // calculate the integral $error
        $dError = (int)($KI * $error * $dt);
        $iError = $iError + $dError;
        // calculate the measurement derivative
        $dpv = ($tempCurrent - $tempCurrentLast) / $dt;
        // calculate the PID output
        $P = (int)($KP * $error); //proportional contribution
        $I = $iError; //integral contribution
        $D = (int)(-$KD * $dpv); //derivative contribution
        $op_ = $P + $I + $D;
        // implement anti-reset windup
        if ($error>0) {
            if ($op_ >= $opHigh) $I = $I - $dError;
        }
        else {
            if ($op_ < $opLow) $I = $I - $dError;
        }
        $op = max($opLow, min($opHigh, $op_));
        $iError = $I;	
        //print("$tempTarget=".$tempTarget." $tempCurrent=".$tempCurrent." $dt=".$dt." $op=".$op." $P=".$P." $I=".$I." $D=".$D);
        return $op;
    }
}

$daemon = new daemonLoopHeating($fileDir . '/tmp');
$daemonActive = $daemon->isDaemonActive();
if ($daemonActive == 0) {
    $daemon->run();
} else {
    logger::writeLog('Невозможно запустить демона daemonLoopHeating, код возврата - ' . $daemonActive,
        loggerTypeMessage::ERROR, loggerName::ERROR);
}
