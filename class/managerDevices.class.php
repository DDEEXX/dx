<?php
/** Менеджер физ. устройств
 * Created by PhpStorm.
 * User: root
 * Date: 23.11.18
 * Time: 15:01
 */

require_once(dirname(__FILE__)."/sqlDataBase.class.php");
//require_once(dirname(__FILE__) . "/managerDevice.interface.php");
//require_once(dirname(__FILE__) . "/managerTemperatureSensor.class.php");
//require_once(dirname(__FILE__) . "/managerVoltageSensor.class.php");
require_once(dirname(__FILE__)."/lists.class.php");
require_once(dirname(__FILE__)."/device.class.php");

class managerException extends Exception {

    public function __construct($mess) {
        parent::__construct($mess);
        error_log($this->__toString(), 0);
    }

    /**
     * Возвращает описание ошибки выполнения SELECT запроса в виде html для вывода на странице
     * @return string
     */
    public function getErrorInfoHTML() {
        $txt = "<h1>ошибка при работе с физическими устройствами.</h1>";
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
            throw new \Exception("Неверный тип продукта");
        }

    }

}

class managerDevices
{

    /**
     * Возвращает объект ассоциированный с определенным физ. устройсвом
     * @param array $value
     * @return mixed
     * @throws Exception
     */
    public static function createDevice(array $value) {

        // Здесь создаём продукт с помощью Фабричного метода
        $device = deviceFactory::build($value);

        return $device;

    }

    /**
     * Добавляет в базу данных новое физ. устройство
     * @param iDevice $device
     * @return bool
     */
    public static function addDevice(iDevice $device) {

        return $device->addInBD();

    }

    /**
     * возвращает объект физ. устройства по его ID либо вызывает исключение
     * @param $idDevice
     * @return mixed
     * @throws connectDBException
     * @throws querySelectDBException
     * @throws managerException
     */
    public static function getDevice($idDevice) {
        $conn = sqlDataBase::Connect();
        $query = 'SELECT * FROM `tdevice` WHERE DeviceID = '.$idDevice;
        $arDevice = queryDataBase::getOne($conn, $query);
        if (is_null($arDevice)) {
            throw new managerException('не могу создать объект физ. устройства по его ID');
        }
        $device = self::createDevice($arDevice);
        return $device;
    }

    /**
     * Возвращает массив с именами менеджеров устройств (имена классов)
     * @return array
     */
    public static function arrayManagersDevices(){
        $aManagersDevices = ['managerTemperatureSensor', 'managerVoltageSensor'];
        return $aManagersDevices;
    }

    /**
     * Проверяем есть ли менеджер с именем и возвращет это имя либо вызывает исключение (для отладки)
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
     * Получить физ. устройства как объекты в виде массива
     * @param Iterator|null $sel
     * @return listDevices
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public static function getListDevices(Iterator $sel = null){

        $arr = DB::getListDevices($sel);
        $list = new listDevices();
        foreach ($arr as $value) {
            $Device = self::createDevice($value);
            $list->append($Device);
        }
        return $list;
    }

}