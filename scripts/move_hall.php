<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:49
 */

// проверить!!! Если день и включить через сайт, то при движении тут же гаснет.
// Если ночью включить через сайт, никогда не гаснет

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/lists.class.php');
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../class/sunInfo.class.php');

class move_hall
{

    const NAME_MOVE = 'move_1';
    const NAME_LIGHT_N = 'light_hol_2_n';
    const MOVE_TIME_N = 8; //через сколько секунд выключится подсветка после отсутствия движения при включении от датчика движения
    const MOVE_TIME_GLOBAL = 1200; //через сколько секунд выключится подсветка независимо каким образом она была включена

    const NAME_LIGHT_3 = 'light_stairs_3'; //Временно, чтобы еще включалась и подсветка лестницы

    static function start()
    {

        $unitLightStairs3 = managerUnits::getUnitLabel(self::NAME_LIGHT_3); //временно

        $unitMove = managerUnits::getUnitLabel(self::NAME_MOVE);
        $unitNightLight = managerUnits::getUnitLabel(self::NAME_LIGHT_N);

        if (is_null($unitMove) || is_null($unitNightLight)) {
            unset($unitMove);
            unset($unitNightLight);
            unset($unitLightStairs3); //временно
            return;
        }

        $moveData = json_decode($unitMove->getValues(), true);
        //Есть движение
        $isMove = $moveData['value'];
        //Время когда состояние датчика изменилось
        $timeNoMove = $moveData['dataValue'];

        //Получить данные с подсветки
        $nightLightData = json_decode($unitNightLight->getValues(), true);
        $isLight = $nightLightData['value'];     //Свет горит
        $statusKey = $nightLightData['status'];     //Статус ключа - каким образом включилась подсветка или вообще выключена
        $timeKey = $nightLightData['dataStatus']; //Когда это было

        $outTime = 99999; //Прошло секунд с момента последней записи состояния подсветки
        if (!is_null($timeKey)) {
            $outTime = time() - strtotime($timeKey);
        }

        //Часть дня - ночь/утро/день/вечер
        $sunInfo = sunInfo::getSunInfo(mktime());

        //для отладки
        //echo 'Move '.$isMove.', Light '.$isLight.' Status '.$statusKey.' Sun '.$sunInfo.' Time '.date("Y-m-d H:i:s", $timeNoMove).PHP_EOL;
        //$sunInfo = dayPart::NIGHT;

        if ($sunInfo == dayPart::NIGHT) { // ночь
            if ($isMove) { // есть движение
                if (!$isLight) { // свет не горит
                    $unitNightLight->updateValue(1, statusKey::MOVE); // включает, записываем что от датчика
                    $unitLightStairs3->updateValue(1, statusKey::MOVE); // включает, записываем что от датчика
                }
            } else { // нет движения
                if ($isLight) { // горит свет

                    //Определяем сколько секунд прошло после отключения датчика движения
                    $moveTime = 99999; // если не известно когда изменилось состояние датчика движения
                    if (!is_null($timeNoMove)) {
                        $moveTime = time() - $timeNoMove;
                    }

                    if ($statusKey == statusKey::MOVE) { // включился датчиком движения
                        if ($moveTime > self::MOVE_TIME_N) { // время вышло с последнего отсутствия движения
                            $unitNightLight->updateValue(0, statusKey::OFF); //гасим
                            $unitLightStairs3->updateValue(0, statusKey::OFF); //гасим
                        }
                    } else { // свет включили вручную (через сайт) ???
                        if ($outTime > self::MOVE_TIME_GLOBAL) { // время вышло с последней активности подсветки
                            $unitNightLight->updateValue(0, statusKey::OFF); //гасим
                            $unitLightStairs3->updateValue(0, statusKey::OFF); //гасим
                        }
                    }
                }
            }
        } else { // светло
            if ($isLight) { // горит свет
                if ($statusKey == statusKey::MOVE) { // включился датчиком движения
                    $unitNightLight->updateValue(0, statusKey::OFF); //гасим
                    $unitLightStairs3->updateValue(0, statusKey::OFF); //гасим
                } else { // свет включили вручную (через сайт) ???
                    if ($outTime > self::MOVE_TIME_GLOBAL) { // время вышло с последней активности подсветки
                        $unitNightLight->updateValue(0, statusKey::OFF); //гасим
                        $unitLightStairs3->updateValue(0, statusKey::OFF); //гасим
                    }
                }
            }
        }

        unset($unitMove);
        unset($unitNightLight);
        unset($unitLightStairs3);
    }

}