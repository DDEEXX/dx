<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:49
 */

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/lists.class.php');
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../class/sunInfo.class.php');

class move_stair
{

    const NAME_MOVE_DOWN  = 'move_stair_down';
    const NAME_MOVE_UP  = 'move_stair_up';
    const NAME_LIGHT = 'light_stair';


    static function start()
    {

        $unitMoveDown = managerUnits::getUnitLabel(self::NAME_MOVE_DOWN);
        $unitMoveUp   = managerUnits::getUnitLabel(self::NAME_MOVE_UP);
        $unitLight    = managerUnits::getUnitLabel(self::NAME_LIGHT);

        if (is_null($unitMoveDown) || is_null($unitMoveUp) || is_null($unitLight)) {
            unset($unitMoveDown);
            unset($unitMoveUp);
            unset($unitLight);
            return;
        }

        $moveDataDown = json_decode($unitMoveDown->getValues(), true);
        $moveDataUp = json_decode($unitMoveUp->getValues(), true);
        //Есть движение
        $isMoveDown = $moveDataDown['value'];
        $isMoveUp = $moveDataUp['value'];
        //Время когда состояние датчика изменилось

        //Часть дня - ночь/утро/день/вечер
        $sunInfo = sunInfo::getSunInfo(mktime());

        if ($sunInfo == dayPart::NIGHT) { // ночь
            if ($isMoveDown) { // есть движение
                $unitLight->updateValue('on_up', statusKey::MOVE); // включает, записываем что от датчика
            }

            if ($isMoveUp) { // есть движение
                $unitLight->updateValue('on_down', statusKey::MOVE); // включает, записываем что от датчика
            }
        }

        unset($unitMoveDown);
        unset($unitMoveUp);
    }

}