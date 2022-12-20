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
        $idDeviceUnitMoveDown = managerUnits::getIdDevice(self::NAME_MOVE_DOWN);
        $idDeviceUnitMoveUp = managerUnits::getIdDevice(self::NAME_MOVE_UP);
        $idDeviceUnitLight = managerUnits::getIdDevice(self::NAME_LIGHT);
        if (is_null($idDeviceUnitMoveDown) || is_null($idDeviceUnitMoveUp) || is_null($idDeviceUnitLight)) {
            return;
        }

        $moveDataDown = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitMoveDown), true);
        $isMoveDown = $moveDataDown['valueNull'] ? 0 : $moveDataDown['value'];

        $moveDataUp = json_decode(managerDevices::getDevicePhysicData($idDeviceUnitMoveUp), true);
        $isMoveUp = $moveDataUp['valueNull'] ? 0 : $moveDataUp['value'];

        //Часть дня - ночь/утро/день/вечер
        $sunInfo = sunInfo::getSunInfo(mktime());

        if ($sunInfo == dayPart::NIGHT) { // ночь
            if ($isMoveDown) { // есть движение
                $deviceLight = managerDevices::getDevice($idDeviceUnitLight);
                $data = json_encode(['value' => 'on_up', 'status' => statusKeyData::MOVE]);
                $deviceLight->setData($data);
                unset($deviceLight);
            }

            if ($isMoveUp) { // есть движение
                $deviceLight = managerDevices::getDevice($idDeviceUnitLight);
                $data = json_encode(['value' => 'on_down', 'status' => statusKeyData::MOVE]);
                $deviceLight->setData($data);
                unset($deviceLight);
            }
        }
    }

}