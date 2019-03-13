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

$NAME_MOVE = 'move_1';
$NAME_LIGHT_N = 'light_hol_2_n';
$MOVE_TIME_N = 12; //через сколько секунд вык. подсветка после отсутствия движения

$unitMove = managerUnits::getUnitLabel($NAME_MOVE);
$unitNightLight = managerUnits::getUnitLabel($NAME_LIGHT_N);

if (is_null($unitMove) || is_null($unitNightLight)) return;

    //Есть движение
    $isMove = $unitMove->getValue();
    //Обновляем в БД значение датчика движения
    $unitMove->updateStatus($isMove);

    //Свет горит
    $isLight = $unitNightLight->getValue(); //

    //Время когда последний раз отключился датчик движения
    $timeNoMove = $unitMove->readLastStatus(0);

    //Часть дня ночь/утро/день/вечер
    $sunInfo = sunInfo::getSunInfo(mktime());

//    $lastTime = strtotime($d->getLastTime($NAME_MOVE, 0));
//    if ($lastTime===false || $lastTime==-1) {
//        $moveTime = 99999;
//    }
//    else {
//        $moveTime = time()-$lastTime;
//    }

    if ($isMove) {
        if ($isLight) {
            if ($sunInfo<>dayPart::NIGHT) {

            }
        }
        else {

        }
    }
    else {
        if ($isLight) {

        }
        else {

        }
    };


unset($unitMove);
unset($unitNightLight);

?>