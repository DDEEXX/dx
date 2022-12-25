<?php
/**
 * Включение/выключение света под лестницей от датчика движения
 */

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/lists.class.php');
require_once(dirname(__FILE__) . '/../class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');

class move_under_stair
{

    const NAME_MOVE = 'move_under_stair';
    const NAME_LIGHT = 'light_under_stair';
    const MOVE_TIME = 15; //через сколько секунд выключится подсветка после отсутствия движения при включении от датчика движения
    const MOVE_TIME_GLOBAL = 1200; //через сколько секунд выключится подсветка независимо каким образом она была включена

    static function start()
    {
        $idDeviceUnitMove = managerUnits::getIdDevice(self::NAME_MOVE);
        $idDeviceUnitLight = managerUnits::getIdDevice(self::NAME_LIGHT);
        if (is_null($idDeviceUnitMove) || is_null($idDeviceUnitLight)) {
            return;
        }
        $moveData = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitMove), true);
        //Есть движение
        $isMove = $moveData['valueNull'] ? 0 : $moveData['value'];
        //Время изменения состояния датчика
        $timeNoMove = $moveData['date'];

        //Получить данные с подсветки
        $lightData = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitLight), true);
        $isLight = $lightData['valueNull'] ? 0 : $lightData['value']; //Свет горит
        $timeKey = $lightData['date']; //Когда это было
        $statusKey = $lightData['status']; //Статус ключа - каким образом включилась подсветка или вообще выключена

        $now = time();
        $outTime = 99999; //Прошло секунд с момента последней записи состояния подсветки
        if ($timeKey > 0) {
            $outTime = $now - $timeKey;
        }

        //для отладки
        //echo 'Move '.$isMove.', Light '.$isLight.' Status '.$statusKey.' Time '.date('Y-m-d H:i:s', $timeNoMove).PHP_EOL;

        if ($isMove) { // есть движение
            if (!$isLight) { // свет не горит
                $deviceLight = managerDevices::getDevice($idDeviceUnitLight);
                $data = json_encode(['value' => 1, 'status' => statusKey::MOVE]);
                $deviceLight->setData($data);
                unset($deviceLight);
            }
        } else { // нет движения
            if ($isLight) { // горит свет

                //Определяем сколько секунд прошло после отключения датчика движения
                $moveTime = 99999; // если не известно когда изменилось состояние датчика движения
                if ($timeNoMove>0) {
                    $moveTime = $now - $timeNoMove;
                }

                if ($statusKey == statusKeyData::MOVE) { // включился датчиком движения
                    if ($moveTime > self::MOVE_TIME) { // время вышло с последнего отсутствия движения
                        $deviceLight = managerDevices::getDevice($idDeviceUnitLight);
                        $data = json_encode(['value' => 0, 'status' => statusKey::OFF]);
                        $deviceLight->setData($data);
                        unset($deviceLight);
                    }
                } else { // свет включили вручную (через сайт) ???
                    if ($outTime > self::MOVE_TIME_GLOBAL) { // время вышло с последней активности подсветки
                        $deviceLight = managerDevices::getDevice($idDeviceUnitLight);
                        $data = json_encode(['value' => 0, 'status' => statusKey::OFF]);
                        $deviceLight->setData($data);
                        unset($deviceLight);
                    }
                }
            }
        }
    }

}