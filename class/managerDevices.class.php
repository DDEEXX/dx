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

class managerException extends Exception {

    public function __construct($mess) {
        parent::__construct($mess);
        error_log($this->__toString(), 0);
    }

    /**
     * ���������� �������� ������ ���������� SELECT ������� � ���� html ��� ������ �� ��������
     * @return string
     */
    public function getErrorInfoHTML() {
        $txt = "<h1>������ ��� ������ � ����������� ������������.</h1>";
        $txt .= "<h2>".$this->GetMessage()."</h2>";
        return $txt;
    }

}

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

    /**
     * ��������� � ���� ������ ����� ���. ����������
     * @param iDevice $device
     * @return bool
     */
    public static function addDevice(iDevice $device) {

        return $device->addInBD();

    }

    /**
     * ���������� ������ ���. ���������� �� ��� ID ���� �������� ����������
     * @param $idDevice
     * @return mixed
     * @throws connectDBException
     * @throws querySelectDBException
     * @throws managerException
     */
    public static function getDevice($idDevice) {
        $conn = sqlDataBase::Connect();
        $query = "SELECT * FROM `tdevice` WHERE DeviceID = $idDevice";
        $arDevice = queryDataBase::getOne($conn, $query);
        if (is_null($arDevice)) {
            throw new managerException('�� ���� ������� ������ ���. ���������� �� ��� ID');
        }
        $device = self::createDevice($arDevice);
        return $device;
    }

    /**
     * ���������� ������ � ������� ���������� ��������� (����� �������)
     * @return array
     */
    public static function arrayManagersDevices(){
        $aManagersDevices = ['managerTemperatureSensor', 'managerVoltageSensor'];
        return $aManagersDevices;
    }

    /**
     * ��������� ���� �� �������� � ������ � ��������� ��� ��� ���� �������� ���������� (��� �������)
     * @param $nameManager
     * @return mixed
     * @throws Exception
     */
    public static function getDeviceManager($nameManager) {
        if (class_exists($nameManager)) {
            return $nameManager;
        } else {
            throw new \Exception("Unknown manager");
        }
    }

    /**
     * �������� ��� ���. ���������� ��� ������� � ���� �������
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