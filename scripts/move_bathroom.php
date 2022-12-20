<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:49
 */

require_once(dirname(__FILE__) . '/../class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/../class/lists.class.php');
require_once(dirname(__FILE__) . '/../class/managerDevices.class.php');
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../class/sunInfo.class.php');

class move_bathroom
{

    const NAME_MOVE = 'move_bathroom';
    const NAME_LIGHT = 'backlight_bathroom';
    const NAME_TOUCH_KEY = 'touch_key1_bathroom';
    const MOVE_TIME = 60;          //через сколько секунд выключится подсветка после отсутствия движения при включении от датчика движения
    const MOVE_TIME_GLOBAL = 1800; //через сколько секунд выключится подсветка независимо каким образом она была включена
    const TIME_OFF_LIGHT = '12000'; //время (в миллисекундах, 1000 - 1 секунда), после поступления команды на выключение, которое надо выдержать, прежде чем отключать подсветку

    static function start()
    {
        $idDeviceUnitMove = managerUnits::getIdDevice(self::NAME_MOVE);
        $idDeviceUnitLight = managerUnits::getIdDevice(self::NAME_LIGHT);
        $idDeviceUitTouchKey = managerUnits::getIdDevice(self::NAME_TOUCH_KEY);
        if (is_null($idDeviceUnitMove) || is_null($idDeviceUnitLight) || is_null($idDeviceUitTouchKey)) {
            return;
        }

        $moveData = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitMove), true);
        //Есть движение
        $isMove = $moveData['valueNull'] ? 0 : $moveData['value'];
        //Время изменения состояния датчика
        $timeNoMove = $moveData['date'];

        //Получить данные кнопки
        $keyData = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitMove), true);
        $keyPress = $keyData['valueNull'] ? 0 : $keyData['value'];

        //Получить данные с подсветки
        $nightLightData = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitLight), true);
        $isLight = $nightLightData['valueNull'] ? 0 : $nightLightData['value']; //Свет горит
        $timeKey = $nightLightData['date']; //Когда это было
        $statusKey = $nightLightData['status'];   //Статус ключа - каким образом включилась подсветка или вообще выключена

        $now = time();
        $outTime = 99999; //Прошло секунд с момента последней записи состояния подсветки
        if ($timeKey > 0) {
            $outTime = $now - $timeKey;
        }

        if ($keyPress) { //Прикоснулись к кнопке выключения
            $deviceLight = managerDevices::getDevice($idDeviceUnitLight);
            $data = json_encode(['value'=>0, 'status'=>statusKey::OFF, 'pause'=>self::TIME_OFF_LIGHT]);
            $deviceLight->setData($data);
            unset($deviceLight);
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
                        // включает, записываем что от датчика
                        $deviceLight = managerDevices::getDevice($idDeviceUnitLight);
                        $data = json_encode(['value'=>1, 'status'=>statusKey::MOVE]);
                        $deviceLight->setData($data);
                        unset($deviceLight);
                    }
                }
            } else { // нет движения
                if ($isLight) { // горит свет

                    //Определяем сколько секунд прошло после отключения датчика движения
                    $moveTime = 99999; // если не известно когда изменилось состояние датчика движения
                    if ($timeNoMove>0) {
                        $moveTime = $now - $timeNoMove;
                    }

                    if ($statusKey == statusKeyData::MOVE) { // включился от датчика движения
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

}