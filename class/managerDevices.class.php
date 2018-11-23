<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 23.11.18
 * Time: 15:01
 */

require_once("class/managerDevice.interface.php");
require_once("class/managerTemperatureSensor.class.php");
require_once("class/managerVoltageSensor.class.php");

const aManagersDevices = ['managerTemperatureSensor', 'managerVoltageSensor'];

class managerDevices
{

    public static function arrayManagersDevices(){
        return aManagersDevices;
    }

    public static function getDeviceManager($nameManager) {
        switch ($nameManager) {
            case 'temperatureSensor' : {
                return 'managerTemperatureSensor';
                break;
            }
        }

        return $nameManager;
    }

}