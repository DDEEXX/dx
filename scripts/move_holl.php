<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:49
 */

require_once(dirname(__FILE__).'/../class/globalConst.interface.php');
require_once(dirname(__FILE__).'/../class/lists.class.php');
require_once(dirname(__FILE__).'/../class/managerUnits.class.php');
require_once(dirname(__FILE__).'/../class/sunInfo.class.php');
require_once(dirname(__FILE__)."/../class/logger.class.php");

$NAME_MOVE = 'move_1';
$NAME_LIGHT_N = 'light_hol_2_n';
$MOVE_TIME_N = 8; //через сколько секунд вык. подсветка после отсутствия движения

$unitMove = managerUnits::getUnitLabel($NAME_MOVE);
$unitNightLight = managerUnits::getUnitLabel($NAME_LIGHT_N);

if (is_null($unitMove) || is_null($unitNightLight)) return;

    //Есть движение
    $isMove = $unitMove->getValue();
    //Обновляем в БД значение датчика движения
    $unitMove->updateStatus($isMove);

    //Свет горит
    $isLight = $unitNightLight->getValue(); //

    //Часть дня ночь/утро/день/вечер
    $sunInfo = sunInfo::getSunInfo(mktime());

    //Время когда последний раз отключился датчик движения
    $timeNoMove = $unitMove->readWhereLastStatus(0);

    if ($timeNoMove===false || is_null($timeNoMove)) {
        $moveTime = 99999;
    }
    else {
        $moveTime = time()-strtotime($timeNoMove);
    }

    $info = 'Move - '.$isMove.' Light - '.$isLight.', Time - '.$moveTime.', Sum - '.$sunInfo;

    if ($isMove) {
        if ($isLight) {
            if ($sunInfo<>dayPart::NIGHT) {
                if ($moveTime>$MOVE_TIME_N) {
                    $unitNightLight->setValue(0);
                    $log = logger::getLogger();
                    $log->log('1 - '.$info, logger::DEFAULTLOGGER);
                    unset($log);
                }
            }
        }
        else {
            if ($sunInfo=dayPart::NIGHT) {
                $unitNightLight->setValue(1);
                $log = logger::getLogger();
                $log->log('2 - '.$info, logger::DEFAULTLOGGER);
                unset($log);
            }
        }
    }
    else {
        if ($isLight) {
            if (true) { //включился от датчика движения
                if ($sunInfo=dayPart::NIGHT) {
                    if ($moveTime>$MOVE_TIME_N) {
                        $unitNightLight->setValue(0);
                        $log = logger::getLogger();
                        $log->log('3 - '.$info, logger::DEFAULTLOGGER);
                        unset($log);
                    }
                }
                else {
                    $unitNightLight->setValue(0);
                    $log = logger::getLogger();
                    $log->log('4 - '.$info, logger::DEFAULTLOGGER);
                    unset($log);
                }
            }
            else {

            }
        }
    };


unset($unitMove);
unset($unitNightLight);

?>