<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 23.11.18
 * Time: 15:01
 */

require_once(dirname(__FILE__)."/sqlDataBase.class.php");
require_once(dirname(__FILE__)."/managerDevice.interface.php");
require_once(dirname(__FILE__)."/managerTemperatureSensor.class.php");
require_once(dirname(__FILE__)."/managerVoltageSensor.class.php");
require_once(dirname(__FILE__)."/lists.class.php");

class managerDevices
{

    public static function arrayManagersDevices(){
        $aManagersDevices = ['managerTemperatureSensor', 'managerVoltageSensor'];
        return $aManagersDevices;
    }

    public static function getDeviceManager($nameManager) {
        if (class_exists($nameManager)) {
            return $nameManager;
        } else {
            throw new \Exception("Unknown manager");
        }
    }

    /**
     * ѕолучить список всех физ. устройств в виде массива
     */
    public static function getListDevices($sel = null){
        return DB::getListDevices($sel);
    }

}