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

$fileDir = dirname(__FILE__).'/..';

require($fileDir . '/class/daemon.class.php');
require($fileDir . '/class/managerUnits.class.php');
require($fileDir . '/class/pidTemperature.class.php');
require_once($fileDir . '/class/mqtt.class.php');

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
    const PAUSE_B = 30; //Пауза в цикле отопления котла (сек)
    const PAUSE_BOILER_DATA = 10; //Пауза между диалогом с котлом отопления
    const PAUSE_F = 60; //Пауза в цикле теплых полов (сек)
//    const FLOOR_ON = 45; //Значение термо головки на 100%
//    const FLOOR_OFF = 5; //Значение термо головки на 0%
    const INTERVAL_UPDATE_BOILER_DATA = 600; //пауза между обновлениями данных (топиков) котла отопления

    public function __construct($dirPidFile)
    {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run()
    {
        parent::run();

        $startTime = time();

        $nextStepBoilerData = $startTime + self::PAUSE_BOILER_DATA;
        $doStepBoilerData = false;

        $nextStepB = $startTime + self::PAUSE_B;
        $predStepB = $startTime - self::PAUSE_B;
        $doStepB = false;
        $boilerTempCurrentLast = 20;
        $iErrorB = 0;
        $b_op = 0;

        $nextStepF = $startTime + self::PAUSE_F;
        $predStepF = $startTime - self::PAUSE_F;
        $doStepF = false;
        $floorTempCurrentLast = 30;
        $iErrorF = 0;
        $fCurValve = 0; //текущее положение головки

        $mqtt = mqttSend::connect('heating');

        $previousUpdateBoilerData = $startTime;
        $topicBoilerSet = $this->updateBoilerTopicSet();
        $topicFloorSet = $this->updateFloorTopicSet();

        while (!$this->stopServer()) {

            $now = time();

            if ($now > $nextStepB) {
                $nextStepB = $startTime + (((int)(($now - $startTime) / self::PAUSE_B)) + 1) * self::PAUSE_B;
                $doStepB = false;
            }

            if ($now > $nextStepF) {
                $nextStepF = $startTime + (((int)(($now - $startTime) / self::PAUSE_F)) + 1) * self::PAUSE_F;
                $doStepF = false;
            }

            if ($now > $nextStepBoilerData) {
                $nextStepBoilerData = $startTime + (((int)(($now - $startTime) / self::PAUSE_BOILER_DATA)) + 1) * self::PAUSE_BOILER_DATA;
                $doStepBoilerData = false;
            }

            //обновляем данные в бойлере
            if ($now < $nextStepBoilerData && !$doStepBoilerData) {
                $doStepBoilerData = true;
                $this->checkBoilerData($mqtt, $topicBoilerSet, $b_op);
            }

            //выполняем алгоритм управления отопление
            if ($now < $nextStepB && !$doStepB) {
                $doStepB = true; $dt = ($now - $predStepB); $predStepB = $now;
                $optionsPID = $this->getLastHeatingData();
                $dataBoiler = $this->getLastBoilerData();
                if (is_null($dataBoiler)) return;

                $ch = $dataBoiler->ch;
                $_spr = $dataBoiler->_spr;

                $log = [];
                //исправление лога, если температура подачи = 0, потеряна связь с котлом, пишем 20
                $log['b_ch'] = $ch == 0 ? 20 : round($ch, 2); //подача контура СО
                $log['b_tar'] = round($_spr, 2); //целевая

                //Управление котлом отопления
                if ($dataBoiler->_mode == boilerMode::MQTT) {

                    $b_op = $this->boiler($optionsPID, $_spr, $boilerTempCurrentLast, $iErrorB, $dt, $log);
                    $b_op = (int)(round($b_op));

                    if (strlen($topicBoilerSet)) {
//                        if ($b_op != $dataBoiler->tset) {
                            $payload = json_encode(['tset' => $b_op]);
                            usleep(100000); //0.1 sec
                            $mqtt->publish($topicBoilerSet, $payload);
//                        }
                    }
                }
                else { //пишем только лог

                    //температура в помещение для отопления
                    $boiler_in = $optionsPID->get('b_tIn');
                    $boiler_in1 = $optionsPID->get('b_tIn1');
                    $boilerCurrentInT = 20;
                    $flagTemp = false; //флаг есть актуальная температура
                    $this->getTemp($boiler_in, $boilerCurrentInT, $flagTemp);
                    if (!$flagTemp) $this->getTemp($boiler_in1, $boilerCurrentInT, $flagTemp);
                    $log['b_cur'] = round($boilerCurrentInT, 2);

                    $log['b_op'] = round($dataBoiler->tset, 2);
                    $log['b_P'] = 0;
                    $log['b_I'] = 0;
                    $log['b_D'] = 0;
                    $log['b_hi'] = round($dataBoiler->_chm, 2);
                    $log['b_lo'] = 0;
                }
                $this->saveInJournal(json_encode($log), 'bl');
            }

            //Управление теплыми полами
            if ($now < $nextStepF && !$doStepF) {
                $doStepF = true; $dtF = ($now - $predStepF); $predStepF = $now;

                $optionsPID = $this->getLastHeatingData();
                $dataFloor1= $this->getLastFloorData();

                if ($optionsPID->get('f_pwr')) {
                    $log = [];

                    $optionsFloor1 = (int)($optionsPID->get('f_mode'));

                    if ($optionsFloor1 == 0) { //режим ПИД регулятора
                        $fCurValve = $dataFloor1->current_heating_setpoint == '{"state":"off"}' ? 1 : 0;

                        $f_op = $this->floor_1($optionsPID, $floorTempCurrentLast, $iErrorF, $dtF, $log);
                        $fTarValve = null; //положение не меняем
                        if (round($f_op, 1) > round($floorTempCurrentLast, 1)) $fTarValve = 1;
                        elseif (round($f_op, 1) < round($floorTempCurrentLast, 1) - 0.2) $fTarValve = 0;

                        if (!is_null($fTarValve)) {
                            if ($fCurValve != $fTarValve) {
                                if ($fTarValve) $payload = '{"state":"off"}'; //открываем головку
                                else $payload = '{"state":"on"}'; //закрываем головку

                                if (strlen($topicFloorSet)) {
                                    $mqttF = mqttSend::connect('heatingF'.time());
                                    usleep(100000); //0.1 sec
                                    $mqttF->publish($topicFloorSet, $payload);
                                    unset($mqttF);
                                    $log['sent'] = $fTarValve ? 1 : 0;
                                }

                            }
                            else $log['sent'] = '_';
                        }
                        else $log['sent'] = 'null';

                    }
                    elseif ($optionsFloor1 == 2) { //ручной режим головка сама регулирует

                        $spr = $optionsPID->get('f_spr');
                        $in = $optionsPID->get('b_tfIn');
                        $in1 = $optionsPID->get('b_tfIn1');

                        //температура в зале по умолчанию держим 25
                        $currentInT = 25;
                        $flagTemp = false; //флаг есть актуальная температура
                        $this->getTemp($in, $currentInT, $flagTemp);
                        if (!$flagTemp) $this->getTemp($in1, $currentInT, $flagTemp);

                        $fCurValve = $dataFloor1->current_heating_setpoint;
                        //if ($fCurValve != $spr) {
                            if (strlen($topicFloorSet)) {
                                $payload = '{"current_heating_setpoint":'.$spr.'}';
                                usleep(100000); //0.1 sec
                                $mqtt->publish($topicFloorSet, $payload);
                            }
                        //}

                        $local_temperature_calibration = (int)(round($currentInT - $dataFloor1->local_temperature, 0));
                        //if ($local_temperature_calibration != $dataFloor1->local_temperature_calibration) {
                            if (strlen($topicFloorSet)) {
                                $payload = '{"local_temperature_calibration":'.$local_temperature_calibration.'}';
                                usleep(100000); //0.1 sec
                                $mqtt->publish($topicFloorSet, $payload);
                            }
                        //}

                    }

                    $log['f_val'] = $fCurValve;
                    $this->saveInJournal(json_encode($log), 'fl');
                }
            }

            if ($now - $previousUpdateBoilerData > self::INTERVAL_UPDATE_BOILER_DATA) {
                $previousUpdateBoilerData = $now;
                $topicBoilerSet = $this->updateBoilerTopicSet();
                $topicFloorSet = $this->updateFloorTopicSet();
            }

            sleep(1); //ждем

            pcntl_signal_dispatch(); //Вызывает обработчики для ожидающих сигналов
        }
    }

    private function checkBoilerData($mqtt, $topic, $b_op) {
        $optionsPID = $this->getLastHeatingData();
        $dataBoiler = $this->getLastBoilerData();
        if (is_null($dataBoiler)) return;
        if ($dataBoiler->_mode == boilerMode::MQTT || $dataBoiler->_mode == boilerMode::MANUAL) {
            // включение СО
            if ($optionsPID->get('b_pwr') != $dataBoiler->_chena) {
                if (strlen($topic)) {
                    $payload = json_encode(['_chena' => $optionsPID->get('b_pwr')]); //!!!!!!
                    $mqtt->publish($topic, $payload);
                    if ($dataBoiler->_mode == boilerMode::MQTT) {
                        $payload = json_encode(['tset' => $b_op]);
                        usleep(100000); //0.1 sec
                        $mqtt->publish($topic, $payload);
                    }
                }
            }
        }
        //целевая температура помещения/теплоносителя
        if ($optionsPID->get('b_spr') != $dataBoiler->_spr) {
            $payload = json_encode(['_spr' => $optionsPID->get('b_spr')]);
            usleep(100000); //0.1 sec
            $mqtt->publish($topic, $payload);
        }
        //включение ГВС
        if ($optionsPID->get('w_pwr') != $dataBoiler->_dhwena) {
            if (strlen($topic)) {
                $payload = json_encode(['_dhwena' => $optionsPID->get('w_pwr')]); //!!!!!!
                usleep(100000); //0.1 sec
                $mqtt->publish($topic, $payload);
            }
        }
        //целевая температура ГВС
        if ($optionsPID->get('w_spr') != $dataBoiler->_dhw) {
            $payload = json_encode(['_dhw' => $optionsPID->get('w_spr')]);
            usleep(100000); //0.1 sec
            $mqtt->publish($topic, $payload);
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

    private function boiler($op, $spr, &$tempCurrentLast, &$boiler_iError, $dt, &$log)
    {
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
            $spr,
            $boilerCurrentInT,
            $tempCurrentLast,
            $boiler_iError,
            $dt,
            $boiler_Kp,
            $boiler_Ki,
            $boiler_Kd,
            $opHigh,
            $opLow,
            $log);
        $tempCurrentLast = $boilerCurrentInT;
        return $op;
    }

    private function PID($tempTarget, $tempCurrent, $tempCurrentLast, &$iError, $dt, $KP, $KI, $KD, $opHigh, $opLow, &$log)
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
        if ($error > 0) {
            if ($op_ >= $opHigh) $I = $I - $dError;
        } else {
            if ($op_ < $opLow) $I = $I - $dError;
        }
        $op = round(max($opLow, min($opHigh, $op_)), 2);
        $iError = $I;

        $log['b_cur'] = round($tempCurrent, 2);
        $log['b_op'] = round($op);
        $log['b_P'] = round($P, 2);
        $log['b_I'] = round($I, 2);
        $log['b_D'] = round($P, 2);
        $log['b_hi'] = round($opHigh, 2);
        $log['b_lo'] = round($opLow, 2);
        return $op;
    }

    private function floor_1($options, &$tempCurrentLast, &$iError, $dt, &$log)
    {
        $Kp = $options->get('f_kp');
        $Ki = $options->get('f_ki');
        $Kd = $options->get('f_kd');
        $target = $options->get('f_tar');
        $cur = $options->get('f_cur');
        $dK = $options->get('f_dK');
        $dT = $options->get('f_dT');
        $out = $options->get('b_tOut');
        $out1 = $options->get('b_tOut1');
        $in = $options->get('b_tfIn');
        $in1 = $options->get('b_tfIn1');
        $spr = $options->get('f_spr');

        //температура обратки теплого пола
        $CurrentInT = 30;
        $flagTemp = false; //флаг есть актуальная температура
        $this->getTemp($in, $CurrentInT, $flagTemp);
        if (!$flagTemp) $this->getTemp($in1, $CurrentInT, $flagTemp);

        //Температура на улице
        $currentOutT = -10;
        $flagTempOut = false; //флаг есть актуальная температура
        $this->getTemp($out, $currentOutT, $flagTempOut);
        if (!$flagTempOut) $this->getTemp($out1, $currentOutT, $flagTempOut);

        $opHigh = 100;

        $pid = new pidTemperature($target);
        $pid->setCurve($cur, $dK, $dT);
        $opLow = $pid->getTempCurve($CurrentInT, $currentOutT);

        $op = $this->PIDf(
            $spr,
            $CurrentInT,
            $tempCurrentLast,
            $iError,
            $dt,
            $Kp,
            $Ki,
            $Kd,
            $opHigh,
            $opLow,
            $log);
        $tempCurrentLast = $CurrentInT;
        return $op;
    }

    private function PIDf($tempTarget, $tempCurrent, $tempCurrentLast, &$iError, $dt, $KP, $KI, $KD, $opHigh, $opLow, &$log)
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
        if ($error > 0) {
            if ($op_ >= $opHigh) $I = $I - $dError;
        } else {
            if ($op_ < $opLow) $I = $I - $dError;
        }
        $op = round(max($opLow, min($opHigh, $op_)), 2);
        $iError = $I;

        $log['f_tar'] = round($tempTarget, 2);
        $log['f_cur'] = round($tempCurrent, 2);
        $log['f_op'] = round($op, 2);
        $log['f_P'] = round($P, 2);
        $log['f_I'] = round($I, 2);
        $log['f_D'] = round($P, 2);
        $log['f_hi'] = round($opHigh, 2);
        $log['f_lo'] = round($opLow, 2);
        return $op;
    }

    private function saveInJournal($data, $type = '')
    {
        $currentData = date('Y-m-d H:i:s');

        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции aAlarmMQTT::savePayloadInJournal. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return;
        }

        $query = sprintf('INSERT INTO t_heatingJournal (date, type, data) VALUES (\'%s\', \'%s\', \'%s\')',
            $currentData, $type, $data);

        try {
            $result = queryDataBase::execute($con, $query);
            if (!$result) {
                logger::writeLog('Ошибка при записи в базу данных (daemonLoopHeating.saveInJournal)',
                    loggerTypeMessage::ERROR, loggerName::ERROR);
            }
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при добавлении данных в базу данных (daemonLoopHeating.saveInJournal). '.
                $e->getMessage() .' '.$e->getTraceAsString(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }
    }

    private function updateBoilerTopicSet() {
        $unitBoiler = managerUnits::getUnitLabel('boiler_opentherm');
        $device = $unitBoiler->getDevice();
        if (is_null($device)) return '';
        $devicePhysic = $device->getDevicePhysic();
        return $devicePhysic->getTopicSet();
    }

    private function updateFloorTopicSet() {
        $unitFloor1 = managerUnits::getUnitLabel('heating_floor_1');
        $device = $unitFloor1->getDevice();
        if (is_null($device)) return '';
        $devicePhysic = $device->getDevicePhysic();
        return $devicePhysic->getTopicSet();
    }

    private function getLastHeatingData() {
        $unitPID = managerUnits::getUnitLabel('boiler_pid');
        if (is_null($unitPID)) return null;
        return $unitPID->getOptions();
    }

    private function getLastBoilerData() {
        $unitBoiler = managerUnits::getUnitLabel('boiler_opentherm');
        return is_null($unitBoiler) ? null : $unitBoiler->getData()->value;
    }

    private function getLastFloorData() {
        $unitFloor = managerUnits::getUnitLabel('heating_floor_1');
        return is_null($unitFloor) ? null : $unitFloor->getData()->value;
    }

}

$daemon = new daemonLoopHeating($fileDir . '/tmp');
$daemonActive = $daemon->isDaemonActive();
if ($daemonActive == 0) {
    try {
        $daemon->run();
    } catch (Exception $e) {
        logger::writeLog('Ошибка при работе демона loopHeating.php. ' . $e->getMessage(),
            loggerTypeMessage::FATAL, loggerName::ERROR);
        return;
    }
} else {
    logger::writeLog('Невозможно запустить демона daemonLoopHeating, код возврата - ' . $daemonActive,
        loggerTypeMessage::ERROR, loggerName::ERROR);
}