<?php
/**
 * Включение/выключение ночной подсветки в коридоре второго этажа
 */

// проверить!!! Если день и включить через сайт, то при движении тут же гаснет.
// Если ночью включить через сайт, никогда не гаснет

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/lists.class.php');
require_once(dirname(__FILE__) . '/../class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../class/sunInfo.class.php');

class move_hall_2
{

    const NAME_MOVE = 'move_hall_2';
    const NAME_LIGHT_N = 'light_hol_2_n';
    const MOVE_TIME_N = 6; //через сколько секунд выключится подсветка после отсутствия движения при включении от датчика движения
    const MOVE_TIME_GLOBAL = 1200; //через сколько секунд выключится подсветка независимо каким образом она была включена

    static function start()
    {
        $idDeviceUnitMove = managerUnits::getIdDevice(self::NAME_MOVE);
        $idDeviceUnitNightLight = managerUnits::getIdDevice(self::NAME_LIGHT_N);
        if (is_null($idDeviceUnitMove) || is_null($idDeviceUnitNightLight)) {
            return;
        }

        $moveData = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitMove), true);
        //Есть движение
        $isMove = $moveData['valueNull'] ? 0 : $moveData['value'];
        //Время изменения состояния датчика
        $timeNoMove = $moveData['date'];

        //Получить данные с подсветки
        $nightLightData = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitNightLight), true);
        $isLight = $nightLightData['valueNull'] ? 0 : $nightLightData['value']; //Свет горит
        $timeKey = $nightLightData['date']; //Когда это было
        $statusKey = $nightLightData['status'];   //Статус ключа - каким образом включилась подсветка или вообще выключена

        $now = time();
        $outTime = 99999; //Прошло секунд с момента последней записи состояния подсветки
        if ($timeKey > 0) {
            $outTime = $now - $timeKey;
        }

        //Часть дня - ночь/утро/день/вечер
        $sunInfo = sunInfo::getSunInfo(mktime());
        if ($sunInfo == dayPart::NIGHT) { // ночь
            if ($isMove) { // есть движение
                if (!$isLight) { // свет не горит
                    $deviceLight = managerDevices::getDevice($idDeviceUnitNightLight);
                    $data = json_encode(['value' => 1, 'status' => statusKey::MOVE]);
                    $deviceLight->setData($data);
                    unset($deviceLight);
                }
            } else { // нет движения
                if ($isLight) { // горит свет
                    if ($statusKey == statusKeyData::MOVE) { // включился датчиком движения
                        //определяем сколько секунд прошло после отключения датчика движения
                        $moveTime = 99999; // если не известно когда изменилось состояние датчика движения
                        if ($timeNoMove>0) {
                            $moveTime = $now - $timeNoMove;
                        }
                        if ($moveTime > self::MOVE_TIME_N) { // время вышло с последнего отсутствия движения
                            $deviceLight = managerDevices::getDevice($idDeviceUnitNightLight);
                            $data = json_encode(['value' => 0, 'status' => statusKey::OFF]);
                            $deviceLight->setData($data);
                            unset($deviceLight);
                        }
                    } else { // свет включили вручную (через сайт) ???
                        if ($outTime > self::MOVE_TIME_GLOBAL) { // время вышло с последней активности подсветки
                            $deviceLight = managerDevices::getDevice($idDeviceUnitNightLight);
                            $data = json_encode(['value' => 0, 'status' => statusKey::OFF]);
                            $deviceLight->setData($data);
                            unset($deviceLight);
                        }
                    }
                }
            }
        } else { // светло
            if ($isLight) { // горит свет
                if ($statusKey == statusKeyData::MOVE) { // включился датчиком движения
                    $deviceLight = managerDevices::getDevice($idDeviceUnitNightLight);
                    $data = json_encode(['value' => 0, 'status' => statusKey::OFF]);
                    $deviceLight->setData($data);
                    unset($deviceLight);
                } else { // свет включили вручную (через сайт) ???
                    if ($outTime > self::MOVE_TIME_GLOBAL) { // время вышло с последней активности подсветки
                        $deviceLight = managerDevices::getDevice($idDeviceUnitNightLight);
                        $data = json_encode(['value' => 0, 'status' => statusKey::OFF]);
                        $deviceLight->setData($data);
                        unset($deviceLight);
                    }
                }
            }
        }
    }

}