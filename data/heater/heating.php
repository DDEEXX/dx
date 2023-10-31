<?php

if (!isset($_REQUEST['dev'])) return;

require_once(dirname(__FILE__) . '/../../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../../class/mqtt.class.php');
require_once(dirname(__FILE__) . '/../../class/pidTemperature.class.php');

function getTemp($label, &$currentTemperature, &$flagActualTemperature)
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

function getDataBoiler($label)
{
    $unitBoiler = managerUnits::getUnitLabel($label);
    if (is_null($unitBoiler)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return new stdClass();
    }
    $boilerData = $unitBoiler->getData();
    $value = $boilerData->value;
    $value->date = $boilerData->date;
    return $value;
}

if ($_REQUEST['dev'] == 'boiler') {
    $label = $_REQUEST['label'];
    $data = getDataBoiler($label);
    header('Content-Type: application/json');
    echo json_encode($data);
} elseif ($_REQUEST['dev'] == 'check_boilerStatus') {
    $result = ['update' => false];
    $label = $_REQUEST['label'];
    $dateStatus = (int)$_REQUEST['dateStatus'];
    $unit = managerUnits::getUnitLabel($label);
    if (!is_null($unit)) {
        $unitData = $unit->getData();
        $dateLastStatus = $unitData->date;
        if ($dateStatus != $dateLastStatus) {
            $result['update'] = true;
        }
    }
    header('Content-Type: application/json');
    echo json_encode($result);
} elseif ($_REQUEST['dev'] == 'set') {

    $label = $_REQUEST['label'];
    $p = $_REQUEST['p'];
    $v = $_REQUEST['v'];
    $d = isset($_REQUEST['d']) && is_numeric($_REQUEST['d']) ? (int)$_REQUEST['d'] : 1;

    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) {
        logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден',
            loggerTypeMessage::ERROR, loggerName::ERROR);
        return;
    }
    $curData = $unit->getData();
    $curV = $curData->value->{$p};

    if (is_numeric($curV) && !is_numeric($v)) return;

    switch ($p) {
        case '_dhw' :
        case '_spr' :
            $value = (float)($v / $d);
            break;
        case '_mode' :
            $value = (int)($v);
            break;
        case '_chena' :
        case '_dhwena' :
            $value = $v === 'true';
            break;
        default :
            $value = $v;
    }
    //if ($curV == $value) return;

    $device = $unit->getDevice();
    if (is_null($device)) exit;
    $devicePhysic = $device->getDevicePhysic();
    $topic = $devicePhysic->getTopicSet();
    if (!strlen($topic)) exit;
    $payload = json_encode([$p => $value]);
    $mqtt = mqttSend::connect();
    $mqtt->publish($topic, $payload, 1);
} elseif ($_REQUEST['dev'] == 'setProperty') {

    $unit = managerUnits::getUnitLabel('boiler_pid');
    $op = $unit->getOptions();

    if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'one') {
        $property = $_REQUEST['property'];
        $value = $_REQUEST['value'];
        if (is_numeric($value)) $value = floatval($value);
        $op->set($property, $value);
    } else {
        $data = $_REQUEST['data'];
        $op->setOptions($data);
    }

}
elseif ($_REQUEST['dev'] == 'dialogSetup') {

    if (isset($_REQUEST['data'])) {
        if ($_REQUEST['data'] == 'boilerData') {
            $label = $_REQUEST['label'];
            $unitBoiler = managerUnits::getUnitLabel($label);
            $data = $unitBoiler->getData();
            $jsonData = json_encode($data->value);
            header('Content-Type: application/json');
            echo $jsonData;
            return;
        } elseif ($_REQUEST['data'] == 'curveGraph') { //данные для графика

            $label = $_REQUEST['label'];
            $dataBoiler = getDataBoiler($label);

            $unit = managerUnits::getUnitLabel('boiler_pid');
            $op = $unit->getOptions();

            $boiler_in = $op->get('b_tIn');
            $boiler_in1 = $op->get('b_tIn1');
            $boiler_in_floor = $op->get('b_tfIn');
            $boiler_in_floor1 = $op->get('b_tfIn1');
            $boiler_in = $op->get('b_tIn');
            $boiler_in1 = $op->get('b_tIn1');

            $boilerCurrentInT = 20;
            $flagTemp = false; //флаг есть актуальная температура
            getTemp($boiler_in, $boilerCurrentInT, $flagTemp);
            if (!$flagTemp) getTemp($boiler_in1, $boilerCurrentInT, $flagTemp);

            $boilerCurrentInTf = 20;
            $flagTempF = false; //флаг есть актуальная температура
            getTemp($boiler_in_floor, $boilerCurrentInTf, $flagTempF);
            if (!$flagTempF) getTemp($boiler_in_floor1, $boilerCurrentInTf, $flagTempF);

            $boiler_target = $op->get('b_tar');
            $boiler_cur = $op->get('b_cur');
            $boiler_dK = $op->get('b_dK');
            $boiler_dT = $op->get('b_dT');
            $boiler_target1 = $op->get('b_tar1');
            $boiler_cur1 = $op->get('b_cur1');
            $boiler_dK1 = $op->get('b_dK1');
            $boiler_dT1 = $op->get('b_dT1');
            $floor_target = $op->get('f_tar');
            $floor_cur = $op->get('f_cur');
            $floor_dK = $op->get('f_dK');
            $floor_dT = $op->get('f_dT');

            $pid1 = new pidTemperature(20);
            $pid1->setCurve(1, 0, 0);

            $pid_b = new pidTemperature($boiler_target);
            $pid_b->setCurve($boiler_cur, $boiler_dK, $boiler_dT);
            $pid_b1 = new pidTemperature($boiler_target1);
            $pid_b1->setCurve($boiler_cur1, $boiler_dK1, $boiler_dT1);
            $pid_f = new pidTemperature($floor_target);
            $pid_f->setCurve($floor_cur, $floor_dK, $floor_dT);

            $tags = [];
            $data1 = [];
            $data_b = [];
            $data_b1 = [];
            $data_f = [];
            for ($i = 20; $i >= -30; $i--) {
                if ($i % 10 == 0) {
                    $tags[] = strval($i);
                    $data1[] = $pid1->getTempCurve(20, $i);
                    if ($dataBoiler->_mode == boilerMode::MQTT) {
                        $data_b[] = $pid_b->getTempCurve($boilerCurrentInT, $i);
                        $data_b1[] = $pid_b1->getTempCurve($boilerCurrentInT, $i);
                    }
                    $data_f[] = $pid_f->getTempCurve($boilerCurrentInTf, $i);
                }
            }
            $jsonData = json_encode(['tags' => $tags, 'data1' => $data1, 'data_b' => $data_b, 'data_b1' => $data_b1, 'data_f' => $data_f]);
            header('Content-Type: application/json');
            echo $jsonData;
            return;
        }
    }

    $label = $_REQUEST['label'];
    $unitBoiler = managerUnits::getUnitLabel($label);
    $data = $unitBoiler->getData();
    $value = $data->value;

    $mode = $value->_mode;

    echo '<script src="js2/boilerSetup.js"></script>';
    echo '<div style="display: flex; justify-content: space-between; margin-top: 10px">';
    echo '        <div style="margin-right: 10px">';
    echo '            <p1 style="font-size: 125%">Режим котла</p1>';
    echo '        </div>';
    echo '        <div id="boiler_mode_radio_group">';
    echo '            <label for="boiler_mode_0">MQTT</label>';
    echo '            <input type="radio" name="boiler_mode_radio" id="boiler_mode_0" value="0" ' . ($mode == 0 ? 'checked="checked"' : '') . '>';
    echo '            <label for="boiler_mode_1">ПИД</label>';
    echo '            <input type="radio" name="boiler_mode_radio" id="boiler_mode_1" value="1" ' . ($mode == 1 ? 'checked="checked"' : '') . '>';
    echo '            <label for="boiler_mode_2">ПЗА</label>';
    echo '            <input type="radio" name="boiler_mode_radio" id="boiler_mode_2" value="2" ' . ($mode == 2 ? 'checked="checked"' : '') . '>';
    echo '            <label for="boiler_mode_3">Ручной</label>';
    echo '            <input type="radio" name="boiler_mode_radio" id="boiler_mode_3" value="3" ' . ($mode == 3 ? 'checked="checked"' : '') . '>';
    echo '            <label for="boiler_mode_4">Выкл</label>';
    echo '            <input type="radio" name="boiler_mode_radio" id="boiler_mode_4" value="4" ' . ($mode == 4 ? 'checked="checked"' : '') . '>';
    echo '        </div>';
    echo '        <span style="margin-left: 15px; font-size: 80%; width: 35%">* при режимах отличных от MQTT настройка через клиента котла</span>';
    echo '        <button id="boiler_setup_save_options"></button>';
    echo '</div>';

    $unitPID = managerUnits::getUnitLabel('boiler_pid');
    $op = $unitPID->getOptions();

    $boiler_Kp = $op->get('b_kp');
    $boiler_Ki = $op->get('b_ki');
    $boiler_Kd = $op->get('b_kd');
    $boiler_target = $op->get('b_tar');
    $boiler_cur = $op->get('b_cur');
    $boiler_dK = $op->get('b_dK');
    $boiler_dT = $op->get('b_dT');
    $floor_Kp = $op->get('f_kp');
    $boiler_target1 = $op->get('b_tar1');
    $boiler_cur1 = $op->get('b_cur1');
    $boiler_dK1 = $op->get('b_dK1');
    $boiler_dT1 = $op->get('b_dT1');
    $floor_Kp1 = $op->get('f_kp1');
    $floor_Ki = $op->get('f_ki');
    $floor_Kd = $op->get('f_kd');
    $floor_target = $op->get('f_tar');
    $floor_cur = $op->get('f_cur');
    $floor_dK = $op->get('f_dK');
    $floor_dT = $op->get('f_dT');
    $boiler_in = $op->get('b_tIn');
    $boiler_in1 = $op->get('b_tIn1');
    $boiler_in_floor = $op->get('b_tfIn');
    $boiler_in_floor1 = $op->get('b_tfIn1');
    $floor_mode = $op->get('f_mode');
    $boiler_out = $op->get('b_tOut');
    $boiler_out1 = $op->get('b_tOut1');

    $boilerCurrentInT = 20;
    $flagTemp = false; //флаг есть актуальная температура
    getTemp($boiler_in, $boilerCurrentInT, $flagTemp);
    if (!$flagTemp) getTemp($boiler_in1, $boilerCurrentInT, $flagTemp);

    $boilerCurrentInTf = 20;
    $flagTempF = false; //флаг есть актуальная температура
    getTemp($boiler_in_floor, $boilerCurrentInTf, $flagTempF);
    if (!$flagTempF) getTemp($boiler_in_floor1, $boilerCurrentInTf, $flagTempF);

    $currentOutT = -10;
    $flagTempOut = false; //флаг есть актуальная температура
    getTemp($boiler_out, $currentOutT, $flagTempOut);
    if (!$flagTempOut) getTemp($boiler_out1, $currentOutT, $flagTempOut);

    $outTemp = sprintf(<<<PID
<div style="display: flex; align-items:center; margin-top: 15px">
    <span>Температура на улице:</span>
    <span class="boiler_setup_input_title">основная</span>
    <input class="ui-corner-all ui-state-default boiler_setup_input" value="%s" property = "b_tOut">
    <span class="boiler_setup_input_title">альтернатива</span>
    <input class="ui-corner-all ui-state-default boiler_setup_input" value="%s" property = "b_tOut1">
    <span class="boiler_setup_input_title">t = %s &degC %s</span>
</div>                
PID
        , $boiler_out, $boiler_out1, $currentOutT, $flagTempOut ? '' : 'неакт.');
    echo $outTemp;

    if ($mode == boilerMode::MQTT) { //MQTT
        //ПИД отопление радиаторы
        echo '<div style="display: flex; align-items:center; margin-top: 15px">';
        echo '    <div style="width: 150px">';
        echo '        <p1>ПИД радиаторы</p1>';
        echo '    </div>';
        echo '    <span >Kp</span>';
        echo '    <div class="boiler_setup_spinner_">';
        echo '      <input id="boiler_setup_boiler_kp" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_Kp . ' property = "b_kp">';
        echo '    </div>';
        echo '    <span>Ki</span>';
        echo '    <div class="boiler_setup_spinner_">';
        echo '      <input id="boiler_setup_boiler_ki" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_Ki . ' property = "b_ki">';
        echo '    </div>';
        echo '    <span>Kd</span>';
        echo '    <div class="boiler_setup_spinner_">';
        echo '      <input id="boiler_setup_boiler_kd" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_Kd . ' property = "b_kd">';
        echo '    </div>';
        echo '</div>';

        $inTemp = sprintf(<<<PID
<div style="display: flex; align-items:center; margin-top: 15px">
    <span>Источник температуры:</span>
    <span class="boiler_setup_input_title">основная</span>
    <input class="ui-corner-all ui-state-default boiler_setup_input" value="%s" property = "b_tIn">
    <span class="boiler_setup_input_title">альтернатива</span>
    <input class="ui-corner-all ui-state-default boiler_setup_input" value="%s" property = "b_tIn1">
    <span class="boiler_setup_input_title">t = %s &degC %s</span>
</div>                
PID
            , $boiler_in, $boiler_in1, $boilerCurrentInT, $flagTemp ? '' : 'неакт.');
        echo $inTemp;
    }

    //ПИД отопление теплые полы
    echo '<div style="display: flex; align-items:center; margin-top: 15px">';
    echo '    <div>';
    echo '        <span>Теплые полы</span>';
    echo '        <div id="boiler_f_mode_radio_group">';
    echo '            <label for="boiler_f_mode_0">ПИД</label>';
    echo '            <input type="radio" name="boiler_floor_mode_radio" id="boiler_f_mode_0" value="0" ' . ($floor_mode == 0 ? 'checked="checked"' : '') . '>';
    echo '            <label for="boiler_f_mode_1">ПЗА</label>';
    echo '            <input type="radio" name="boiler_floor_mode_radio" id="boiler_f_mode_1" value="1" ' . ($floor_mode == 1 ? 'checked="checked"' : '') . '>';
    echo '        </div>';
    echo '    </div>';

    if ($floor_mode == boilerMode::MQTT) {
        echo '    <span class="boiler_setup_input_title">Kp</span>';
        echo '    <div class="boiler_setup_spinner_">';
        echo '      <input id="boiler_setup_floor_kp" class="property_spinner boiler_setup_spinner" value=' .
            $floor_Kp . ' property = "f_kp">';
        echo '    </div>';
        echo '    <span>Ki</span>';
        echo '    <div class="boiler_setup_spinner_">';
        echo '      <input id="boiler_setup_floor_ki" class="property_spinner boiler_setup_spinner" value=' .
            $floor_Ki . ' property = "f_ki">';
        echo '    </div>';
        echo '    <span>Kd</span>';
        echo '    <div class="boiler_setup_spinner_">';
        echo '      <input id="boiler_setup_floor_kd" class="property_spinner boiler_setup_spinner" value=' .
            $floor_Kd . ' property = "f_kd">';
        echo '    </div>';
    }
    echo '</div>';

    $inTempFloor = sprintf(<<<PID
<div style="display: flex; align-items:center; margin-top: 15px">
    <span>Источник температуры:</span>
    <span class="boiler_setup_input_title">основная</span>
    <input class="ui-corner-all ui-state-default boiler_setup_input" value="%s" property = "b_tfIn">
    <span class="boiler_setup_input_title">альтернатива</span>
    <input class="ui-corner-all ui-state-default boiler_setup_input" value="%s" property = "b_tfIn1">
    <span class="boiler_setup_input_title">t = %s &degC %s</span>
</div>
PID
        , $boiler_in_floor, $boiler_in_floor1, $boilerCurrentInTf, $flagTempF ? '' : 'неакт.');
    echo $inTempFloor;

    //Кривые
    echo '<div style="display: flex; margin-top: 20px">';
    echo '    <div style="width: 600px">';
    if ($mode == boilerMode::MQTT) { //MQTT
        echo '        <div style="margin-top: 10px">';
        echo '            <p1>Котел (верхнее ограничение)</p1>';
        echo '        </div>';
        echo '        <div style="display: flex; align-items:center; margin-top: 10px" >';
        echo '            <span>Целевая</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '              <input id="boiler_setup_boiler_tar" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_target . ' property = "b_tar">';
        echo '            </div>';
        echo '            <span>Наклон</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '              <input id="boiler_setup_boiler_cur" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_cur . ' property = "b_cur">';
        echo '            </div>';
        echo '            <span>dK</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '              <input id="boiler_setup_boiler_dK" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_dK . ' property = "b_dK">';
        echo '            </div>';
        echo '            <span>dT</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '              <input id="boiler_setup_boiler_dT" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_dT . ' property = "b_dT">';
        echo '            </div>';
        echo '        </div>';
        echo '        <div style="margin-top: 25px">';
        echo '            <p1>Котел (нижнее ограничение)</p1>';
        echo '        </div>';
        echo '        <div style="display: flex; align-items:center; margin-top: 10px" >';
        echo '            <span>Целевая</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '              <input id="boiler_setup_boiler_tar1" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_target1 . ' property = "b_tar1">';
        echo '            </div>';
        echo '            <span>Наклон</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '            <input id="boiler_setup_boiler_cur1" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_cur1 . ' property = "b_cur1">';
        echo '            </div>';
        echo '            <span>dK</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '            <input id="boiler_setup_boiler_dK1" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_dK1 . ' property = "b_dK1">';
        echo '            </div>';
        echo '            <span>dT</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '            <input id="boiler_setup_boiler_dT1" class="property_spinner boiler_setup_spinner" value=' .
            $boiler_dT1 . ' property = "b_dT1">';
        echo '            </div>';
        echo '        </div>';
    }

        echo '        <div style="margin-top: 25px">';
        if ($floor_mode == 0)
            echo '            <p1>Полы (нижнее ограничение)</p1>';
        else
            echo '            <p1>Полы</p1>';
        echo '        </div>';
        echo '        <div style="display: flex; align-items:center; margin-top: 10px" >';
        echo '            <span>Целевая</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '              <input id="boiler_setup_boiler_cur" class="property_spinner boiler_setup_spinner" value=' .
            $floor_target . ' property = "f_tar">';
        echo '            </div>';
        echo '            <span>Наклон</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '            <input id="boiler_setup_floor_cur" class="property_spinner boiler_setup_spinner" value=' .
            $floor_cur . ' property = "f_cur">';
        echo '            </div>';
        echo '            <span>dK</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '            <input id="boiler_setup_floor_dK" class="property_spinner boiler_setup_spinner" value=' .
            $floor_dK . ' property = "f_dK">';
        echo '            </div>';
        echo '            <span>dT</span>';
        echo '            <div class="boiler_setup_spinner_">';
        echo '            <input id="boiler_setup_floor_dT" class="property_spinner boiler_setup_spinner" value=' .
            $floor_dT . ' property = "f_dT">';
        echo '            </div>';
        echo '        </div>';
        echo '    </div>';
        echo '    <div style="width: 500px"><canvas id="graphCurve"><p>GRAPH</p></canvas></div>';
        echo '</div>';

    $outTempFloorBathroom = sprintf(<<<PID
<div style="margin-top: 25px">
    <p1>Теплые полы (Ванная)</p1>
    <div style="display: flex; align-items:center; margin-top: 15px">
        <span>%s</span>
    </div>
</div>
PID
        , '');
    echo $outTempFloorBathroom;


}
elseif ($_REQUEST['dev'] == 'heatingLog') {

    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

    if (isset($_REQUEST['data'])) {
        if ($_REQUEST['data'] == 'logGraph') { //данные для графика

            $result = null;
            try {
                $con = sqlDataBase::Connect();
                $format = 'SELECT data, DATE_FORMAT(date, \'%s\') date_f FROM t_heatingJournal WHERE type=\'%s\' AND date>=(NOW() - INTERVAL 12 HOUR) AND date<=NOW() ORDER BY date';
                $query = sprintf($format, '%H:%i',$type);
                $result = queryDataBase::getAll($con, $query);
            } catch (connectDBException $e) {
                logger::writeLog('Ошибка при подключении к базе данных в функции getTemperatureForInterval. ' . $e->getMessage(),
                    loggerTypeMessage::FATAL, loggerName::ERROR);
            } catch (querySelectDBException $e) {
                logger::writeLog('Ошибка в функции getTemperatureForInterval. При выполнении запроса ' . $query . '. ' . $e->getMessage(),
                    loggerTypeMessage::FATAL, loggerName::ERROR);
            }
            unset($con);

            $tags = [];
            $dataTar = [];
            $dataCur = [];
            $dataOp = [];
            $dataHi = [];
            $dataLo = [];
            $dataCh = [];

            if (!is_null($result)) {
                foreach ($result as $key=>$val) {
                    $tags[] = $val['date_f'];
                    $dataObj = json_decode($val['data']);
                    $dataTar[] = is_null($dataObj->b_tar) ? 0 : $dataObj->b_tar;
                    $dataCur[] = is_null($dataObj->b_cur) ? 0 : $dataObj->b_cur;
                    $dataOp[] = is_null($dataObj->b_op) ? 0 : $dataObj->b_op;
                    $dataHi[] = is_null($dataObj->b_hi) ? 0 : $dataObj->b_hi;
                    $dataLo[] = is_null($dataObj->b_lo) ? 0 : $dataObj->b_lo;
                    $dataCh[] = is_null($dataObj->b_ch) ? 0 : $dataObj->b_ch;
                }
            }

            $gr_tar = [
                'data' => $dataTar,
                'label' => 'Целевая',
                'borderColor' => 'rgba(38,90,203,0.8)'
            ];
            $gr_cur = [
                'data' => $dataCur,
                'label' => 'Текущая',
                'borderColor' => 'rgba(62,203,38,0.8)'
            ];
            $gr_op = [
                'data' => $dataOp,
                'label' => 'СО, расчет',
                'borderColor' => 'rgba(225,201,45,0.8)'
            ];
            $gr_hi = [
                'data' => $dataHi,
                'label' => 'СО max',
                'borderColor' => 'rgba(227,90,90,0.8)'
            ];
            $gr_lo = [
                'data' => $dataLo,
                'label' => 'СО min',
                'borderColor' => 'rgba(78,90,232,0.8)'
            ];
            $gr_ch = [
                'data' => $dataCh,
                'label' => 'СО подача',
                'borderColor' => 'rgba(87,234,95,0.8)'
            ];

            $data1 = [$gr_tar, $gr_cur];
            $data2 = [$gr_op, $gr_hi, $gr_lo, $gr_ch];

            $jsonData = json_encode(['tags' => $tags, 'data1' => $data1, 'data2' => $data2]);
            header('Content-Type: application/json');
            echo $jsonData;
            return;
        }
    }

    echo '<script src="js2/heatingLog.js"></script>';
    echo '<p1>Отопление</p1>';
    echo '<div><canvas id="graphCurveLog1"><p>GRAPH</p></canvas></div>';
    echo '<div><canvas id="graphCurveLog2"><p>GRAPH</p></canvas></div>';

}
