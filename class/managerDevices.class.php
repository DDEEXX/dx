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
require_once(dirname(__FILE__)."/device.class.php");

class deviceFactory {

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
            throw new \Exception("�������� ��� ��������");
        }

    }

}

class managerDevices
{

    public static function createDevice(array $value) {

        // ����� ������ ������� � ������� ���������� ������
        $device = deviceFactory::build($value);

        return $device;

    }

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
     * �������� ������ ���� ���. ��������� � ���� �������
     * @param Iterator|null $sel
     * @return listDevices
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public static function getListDevices(Iterator $sel = null){

        $arr = DB::getListDevices($sel);
        $list = new listDevices();
        foreach ($arr as $value) {
            $Devices = self::createDevice($value);
            $list->append($Devices);
        }
        return $list;
    }

}