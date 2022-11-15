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

class move_bathroom
{

    const NAME_MOVE = 'move_bathroom';
    const NAME_LIGHT = 'backlight _bathroom';
    const NAME_TOUCH_KEY = 'touch_key1_bathroom';
    const MOVE_TIME = 60;          //через сколько секунд выключится подсветка после отсутствия движения при включении от датчика движения
    const MOVE_TIME_GLOBAL = 1800; //через сколько секунд выключится подсветка независимо каким образом она была включена
    const TIME_OFF_LIGHT = '12000'; //время (в миллисекундах, 1000 - 1 секунда), после поступления команды на выключение, которое надо выдержать, прежде чем отключать подсветку

    static function start()
    {

        $unitMove = managerUnits::getUnitLabel(self::NAME_MOVE);
        $unitLight = managerUnits::getUnitLabel(self::NAME_LIGHT);
        $unitTouchKey = managerUnits::getUnitLabel(self::NAME_TOUCH_KEY);

        if (is_null($unitMove) || is_null($unitLight) || is_null($unitTouchKey)) {
            unset($unitMove);
            unset($unitLight);
            unset($unitTouchKey);
            return;
        }

        $moveData = json_decode($unitMove->getValues(), true);
        //Есть движение
        $isMove = $moveData['value'];
        //Время когда состояние датчика изменилось
        $timeNoMove = $moveData['dataValue'];

        //Получить данные кнопки
        $keyData = json_decode($unitTouchKey->getValues(), true);
        $keyPress = $keyData['value'];

        //Получить данные с подсветки
        $nightLightData = json_decode($unitLight->getValues(), true);
        $isLight = $nightLightData['value'];      //Свет горит
        $statusKey = $nightLightData['status'];   //Статус ключа - каким образом включилась подсветка или вообще выключена
        $timeKey = $nightLightData['dataStatus']; //Когда это было

        $now = time();
        $outTime = 99999; //Прошло секунд с момента последней записи состояния подсветки
        if (!is_null($timeKey)) {
            $outTime = $now - $timeKey;
        }


        if ($keyPress) { //Прикоснулись к кнопке выключения
            $unitLight->updateValue(0, statusKey::OFF, self::TIME_OFF_LIGHT); //гасим
        }
        else {
            if ($isMove) { // есть движение
                if (!$isLight) { // свет не горит
                    //Включение от датчика только
                    // если (ночь) И (время больше 23 часов ИЛИ время меньше (в будние 7 утра, в выходные 8 утра)
                    $sunInfo = sunInfo::getSunInfo(mktime());
                    $today = getdate();
                    $hours = $today['hours'];
                    $weekends = $today['wday'] == 0 || $today['wday'] == 6;
                    if ($sunInfo == dayPart::NIGHT && ($hours>=23 || ($weekends && $hours<8) || (!$weekends && $hours<7))) {
                        $unitLight->updateValue(1, statusKey::MOVE); // включает, записываем что от датчика
                    }
                }
            } else { // нет движения
                if ($isLight) { // горит свет

                    //Определяем сколько секунд прошло после отключения датчика движения
                    $moveTime = 99999; // если не известно когда изменилось состояние датчика движения
                    if (!is_null($timeNoMove)) {
                        $moveTime = $now - $timeNoMove;
                    }

                    if ($statusKey == statusKey::MOVE) { // включился от датчика движения
                        if ($moveTime > self::MOVE_TIME) { // время вышло с последнего отсутствия движения
                            $unitLight->updateValue(0, statusKey::OFF); //гасим
                        }
                    } else { // свет включили вручную (через сайт) ???
                        if ($outTime > self::MOVE_TIME_GLOBAL) { // время вышло с последней активности подсветки
                            $unitLight->updateValue(0, statusKey::OFF); //гасим
                        }
                    }
                }
            }
        }

        unset($unitMove);
        unset($unitNightLight);
        unset($unitTouchKey);

    }

}