<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:49
 */

// проверить!!! Если день и ключить через сайт, то при движениитут же гаснет.
// Если ночью включить через сайт, никогда не гаснет

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/lists.class.php');
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../class/sunInfo.class.php');

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

//Каким образом включилася подсветка или выключена
$statusKey = $unitNightLight->readLastStatusKeyJournal();

//Часть дня ночь/утро/день/вечер
$sunInfo = sunInfo::getSunInfo(mktime());

//Время когда последний раз отключился датчик движения
$timeNoMove = $unitMove->readWhereLastStatus(0);

if ($timeNoMove === false || is_null($timeNoMove)) {
    $moveTime = 99999;
}
else {
    $moveTime = time() - strtotime($timeNoMove);
}

if ($sunInfo = dayPart::NIGHT) { // ночь
    if ($isMove) { // есть движение
        if (!$isLight) { // свет не горит
            $unitNightLight->setValue(1, statusKey::MOVE); // включает, записываем что от датчика
        }
    }
    else { // нет движения
        if ($isLight) { // горит свет
            if ($statusKey == statusKey::MOVE) { // включился датчиком движения
                if ($moveTime > $MOVE_TIME_N) { // время вышло с последнего отсутствия движения
                    $unitNightLight->setValue(0, statusKey::OFF); //гасим
                }
            }
            else { // свет включили вручную (через сайт)
                // это для проверки
                if ($moveTime > $MOVE_TIME_N) { // время вышло с последнего отсутствия движения
                    $unitNightLight->setValue(0, statusKey::OFF); //гасим
                }
            }
        }
    }
}
else { // светло
    if ($isLight) { // горит свет
        if ($statusKey == statusKey::MOVE) { // включился датчиком движения
            $unitNightLight->setValue(0, statusKey::OFF); //гасим
        }
    }
}

//if ($isMove) {
//    if ($isLight) {
//        if ($sunInfo != dayPart::NIGHT) {
//            if ($moveTime > $MOVE_TIME_N) {
//                if ($statusKey == statusKey::MOVE) {
//                    $unitNightLight->setValue(0, statusKey::OFF);
//                }
//            }
//        }
//    }
//    else {
//        if ($sunInfo == dayPart::NIGHT) {
//            $unitNightLight->setValue(1, statusKey::MOVE);
//        }
//    }
//}
//else {
//    if ($isLight) {
//        if ($statusKey == statusKey::MOVE) { //включился от датчика движения
//            if ($sunInfo == dayPart::NIGHT) {
//                if ($moveTime > $MOVE_TIME_N) {
//                    $unitNightLight->setValue(0, statusKey::OFF);
//                }
//            }
//            else {
//                $unitNightLight->setValue(0, statusKey::OFF);
//            }
//        }
//        else {
//            if ($moveTime > $MOVE_TIME_N) {
//                $unitNightLight->setValue(0, statusKey::OFF);
//            }
//        }
//    }
//};

unset($unitMove);
unset($unitNightLight);

?>