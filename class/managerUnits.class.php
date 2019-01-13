<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:11
 */

require_once(dirname(__FILE__)."/sqlDataBase.class.php");
require_once(dirname(__FILE__)."/lists.class.php");

class unitFactory {

    public static function build (array $value) {

        switch ($value['SensorTypeID']) {
            case typeDevice::TEMPERATURE :
                $className = 'temperatureSensor';
                break;
            case typeDevice::VOLTAGE :
                $className = 'voltageSensor';
                break;
            case typeDevice::LABEL :
                $className = 'labelSensor';
                break;
            case typeDevice::KEY_IN :
                $className = 'keyInSensor';
                break;
            case typeDevice::POWER_KEY :
                $className = 'powerKeyMaker';
                break;
            case typeDevice::KEY_OUT :
                $className = 'keyOutMaker';
                break;
            default : $className = '';

        }

        if (class_exists($className)) {
            return new $className($value);
        } else {
            throw new \Exception("Неверный тип продукта");
        }

    }

}

class managerUnits
{

    public static function createDevice(array $value) {

        // Здесь создаём продукт с помощью Фабричного метода
        $unit = unitFactory::build($value);

        return $unit;

    }

    public static function getListUnits(Iterator $sel = null){

        $arr = DB::getListUnits($sel);
        $list = new listUnits();
        foreach ($arr as $value) {
           $Devices = self::createDevice($value);
           $list->append($Devices);
        }
        return $list;

    }

}