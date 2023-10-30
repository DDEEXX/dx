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
require($fileDir . '/class/logger.class.php');

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

                $unitBoiler = managerUnits::getUnitLabel('boiler_pid');
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
//        if ($value->_mode) return; //режим не mqtt
        if ($value->_mode != 1) return; //режим не mqtt

        $op = $unitPID->getOptions();
        $boiler_Kp = $op->get('b_kp');
        if (is_null($boiler_Kp)) $boiler_Kp = 1;
        $boiler_Ki = $op->get('b_ki');
        if (is_null($boiler_Ki)) $boiler_Ki = 0.1;
        $boiler_Kd = $op->get('b_kd');
        if (is_null($boiler_Kd)) $boiler_Kd = 10;
        $boiler_target = $op->get('b_tar');
        if (is_null($boiler_target)) $boiler_target = 23;
        $boiler_cur = $op->get('b_cur');
        if (is_null($boiler_cur)) $boiler_cur = 1;
        $boiler_dK = $op->get('b_dK');
        if (is_null($boiler_dK)) $boiler_dK = 1;
        $boiler_dT = $op->get('b_dT');
        if (is_null($boiler_dT)) $boiler_dT = 1;
        $floor_Kp = $op->get('f_kp');
        if (is_null($floor_Kp)) $floor_Kp = 1;
        $boiler_target1 = $op->get('b_tar1');
        if (is_null($boiler_target1)) $boiler_target1 = 20;
        $boiler_cur1 = $op->get('b_cur1');
        if (is_null($boiler_cur1)) $boiler_cur1 = 1;
        $boiler_dK1 = $op->get('b_dK1');
        if (is_null($boiler_dK1)) $boiler_dK1 = 1;
        $boiler_dT1 = $op->get('b_dT1');
        if (is_null($boiler_dT1)) $boiler_dT1 = 1;
        $boiler_out = $op->get('b_tOut');
        if (is_null($boiler_out)) $boiler_out = '';
        $boiler_out1 = $op->get('b_tOut1');
        if (is_null($boiler_out1)) $boiler_out1 = '';
        $boiler_in = $op->get('b_tIn');
        if (is_null($boiler_in)) $boiler_in = '';
        $boiler_in1 = $op->get('b_tIn1');
        if (is_null($boiler_in1)) $boiler_in1 = '';

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
            $boiler_target,
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
        $l = 'tT=' .$tempTarget. ' tC=' .$tempCurrent. ' op=' .$op. ' op_=' .$op_. ' P=' .$P. ' I=' .$I. ' D=' .$D.' h='.$opHigh.' l = '.$opLow.' dt = '.$dt;
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
