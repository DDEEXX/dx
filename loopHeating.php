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
require($fileDir . '/class/managerUnits.class.php');
require($fileDir . '/class/pidTemperature.class.php');

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
    const PAUSE = 60; //Пауза в основном цикле, в секундах (30 сек)

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run()
    {
        parent::run();

        $startTime = time();
        $nextStep = $startTime + self::PAUSE;
        $predStep = $startTime - self::PAUSE;;
        $doStep = false;
        $boilerTempCurrentLast = null;
        $boiler_iError = 0;

        while (!$this->stopServer()) {

            $now = time();

            if ($now>$nextStep) {
                $nextStep = $startTime + (((int)(($now - $startTime)/self::PAUSE))+1) * self::PAUSE;
                $doStep = false;
            }

            if ($now < $nextStep && !$doStep) {
                //выполняем алгоритм управления отопление
                $doStep = true;
                $dt = ($now - $predStep);
                $predStep = $now;

                $unitBoiler = managerUnits::getUnitLabel('boiler_opentherm');
                $unitPID = managerUnits::getUnitLabel('boiler_pid');
                if (is_null($unitBoiler) || is_null($unitPID)) continue;
                $this->boiler($unitBoiler, $unitPID, $boilerTempCurrentLast, $boiler_iError, $dt);

            }

            sleep(1); //ждем

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов
        }
    }

    private function getTemp($label, &$currentTemperature, &$flagActualTemperature)
    {
        if (!strlen($label)) return;
        $uniteTempIn = managerUnits::getUnitLabel($label);
        if (!is_null($uniteTempIn)) {
            $valueTempIn = $uniteTempIn->getData();
            $actualTimeTemperature = DB::getConst('ActualTimeTemperature');
            if ((time() - $valueTempIn->date) < $actualTimeTemperature && !$valueTempIn->valueNull) {
                $currentTemperature = $valueTempIn->value;
                $flagActualTemperature = true;
            }
        }
    }

    private function boiler($unitBoiler, $unitPID, &$tempCurrentLast, &$boiler_iError, $dt)
    {
        $data = $unitBoiler->getData();
        $value = $data->value;
        if ($value->_mode != boilerMode::MQTT) return;

        $_spr = $value->_spr;

        $op = $unitPID->getOptions();
        $boiler_Kp = $op->get('b_kp');
        $boiler_Ki = $op->get('b_ki');
        $boiler_Kd = $op->get('b_kd');
        $boiler_target = $op->get('b_tar');
        $boiler_cur = $op->get('b_cur');
        $boiler_dK = $op->get('b_dK');
        $boiler_dT = $op->get('b_dT');
        $boiler_target1 = $op->get('b_tar1');
        $boiler_cur1 = $op->get('b_cur1');
        $boiler_dK1 = $op->get('b_dK1');
        $boiler_dT1 = $op->get('b_dT1');
        $boiler_out = $op->get('b_tOut');
        $boiler_out1 = $op->get('b_tOut1');
        $boiler_in = $op->get('b_tIn');
        $boiler_in1 = $op->get('b_tIn1');

        //температура в помещение для отопления
        $boilerCurrentInT = 20;
        $flagTemp = false; //флаг есть актуальная температура
        $this->getTemp($boiler_in, $boilerCurrentInT, $flagTemp);
        if (!$flagTemp) $this->getTemp($boiler_in1, $boilerCurrentInT, $flagTemp);
        if (is_null($tempCurrentLast)) $tempCurrentLast = $boilerCurrentInT;

        //Температура на улице
        $currentOutT = -10;
        $flagTempOut = false; //флаг есть актуальная температура
        $this->getTemp($boiler_out, $currentOutT, $flagTempOut);
        if (!$flagTempOut) $this->getTemp($boiler_out1, $currentOutT, $flagTempOut);

        $pid_b = new pidTemperature($boiler_target);
        $pid_b->setCurve($boiler_cur, $boiler_dK, $boiler_dT);
        $opHigh = $pid_b->getTempCurve($boilerCurrentInT, $currentOutT);

        $pid_b1 = new pidTemperature($boiler_target1);
        $pid_b1->setCurve($boiler_cur1, $boiler_dK1, $boiler_dT1);
        $opLow = $pid_b1->getTempCurve($boilerCurrentInT, $currentOutT);

        $op = $this->PID(
            $_spr,
            $boilerCurrentInT,
            $tempCurrentLast,
            $boiler_iError,
            $dt,
            $boiler_Kp,
            $boiler_Ki,
            $boiler_Kd,
            $opHigh,
            $opLow);
        $tempCurrentLast = $boilerCurrentInT;
    }

    private function PID($tempTarget, $tempCurrent, $tempCurrentLast, &$iError, $dt, $KP, $KI, $KD, $opHigh, $opLow)
    {
        // calculate the $error
        $error = $tempTarget - $tempCurrent;
        // calculate the integral $error
        $dError = round($KI * $error * $dt, 2);
        $iError = $iError + $dError;
        // calculate the measurement derivative
        $dpv = ($tempCurrent - $tempCurrentLast) / $dt;
        // calculate the PID output
        $P = round($KP * $error, 2); //proportional contribution
        $I = $iError; //integral contribution
        $D = round(-$KD * $dpv, 2); //derivative contribution
        $op_ = round($P + $I + $D, 2);
        // implement anti-reset windup
        if ($error>0) {
            if ($op_ >= $opHigh) $I = $I - $dError;
        }
        else {
            if ($op_ < $opLow) $I = $I - $dError;
        }
        $op = round(max($opLow, min($opHigh, $op_)), 2);
        $iError = $I;	
        $l = 'tT='.round($tempTarget,2).' tC='.round($tempCurrent,2).' op='.round($op,2).
            ' op_='.round($op_,2).' P='.round($P,2).' I='.round($I,2).' D='.round($D,2).
            ' h='.round($opHigh,2).' l = '.round($opLow,2).' dt = '.round($dt,2);
        logger::writeLog($l,loggerTypeMessage::NOTICE, loggerName::DEBUG);
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
