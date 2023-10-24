<?php
/** Менеджер физ. устройств
 * Created by PhpStorm.
 * User: root
 * Date: 23.11.18
 * Time: 15:01
 */

require_once(dirname(__FILE__) . '/sqlDataBase.class.php');
require_once(dirname(__FILE__) . '/lists.class.php');
require_once(dirname(__FILE__) . '/device.class.php');
require_once(dirname(__FILE__) . '/sharedMemory.class.php');
require_once(dirname(__FILE__) . '/globalConst.interface.php');

class managerException extends Exception
{

    public function __construct($mess)
    {
        parent::__construct($mess);
        error_log($this->__toString());
    }

    /**
     * Возвращает описание ошибки выполнения SELECT запроса в виде html для вывода на странице
     * @return string
     */
    public function getErrorInfoHTML()
    {
        $txt = '<h1>ошибка при работе с физическими устройствами.</h1>';
        $txt .= '<h2>' . $this->getMessage() . '</h2>';
        return $txt;
    }

}

class deviceFactory
{

    /**
     * @param array $value
     * @return null
     * @throws Exception
     */
    public static function build(array $value)
    {
        switch ($value['DeviceTypeID']) {
            case typeDevice::TEMPERATURE :
                $className = 'temperatureSensorDevice';
                break;
            case typeDevice::LABEL :
                $className = 'labelSensorDevice';
                break;
            case typeDevice::KEY_IN :
                $className = 'keyInSensorDevice';
                break;
            case typeDevice::POWER_KEY :
            case typeDevice::KEY_OUT :
                $className = 'KeyOutMakerDevice';
                break;
            case typeDevice::PRESSURE :
                $className = 'pressureSensorDevice';
                break;
            case typeDevice::HUMIDITY :
                $className = 'humiditySensorDevice';
                break;
            case typeDevice::GAS_SENSOR :
                $className = 'gasSensor';
                break;
            case typeDevice::SWITCH_WHD02 :
                $className = 'zigbeeSwitchWHD02';
                break;
            case typeDevice::KITCHEN_HOOD :
                $className = 'kitchenHood';
                break;
            case typeDevice::BOILER_OPEN_THERM :
                $className = 'boilerOpenTherm';
                break;
            default :
                return null;
        }

        if (class_exists($className)) {
            return new $className($value);
        }
        else {
            throw new Exception('Неверный тип продукта');
        }

    }

}

class managerDevices
{

    /**
     * Возвращает объект ассоциированный с определенным физ. устройством
     * @param array $value
     * @return iDevice|null
     */
    private static function createDevice(array $value)
    {
        //Здесь создаём продукт с помощью Фабричного метода
        try {
            $device = deviceFactory::build($value);
        } catch (Exception $e) {
            $device = null;
        }
        return $device;
    }

    /**
     * Возвращает объект устройства по его ID
     * @param $idDevice
     * @return iSensorDevice|iMakerDevice|null
     */
    public static function getDevice($idDevice)
    {
        try {
            $conn = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            return null;
        }
        $query = 'SELECT * FROM tdevice  WHERE DeviceID = ' . $idDevice;
        try {
            $arDevice = queryDataBase::getOne($conn, $query);
        } catch (querySelectDBException $e) {
            return null;
        }
        return self::createDevice($arDevice);
    }

    /**
     * Получить физ. устройства как объекты в виде массива
     * @param Iterator|null $sel
     * @return listDevices
     */
    public static function getListDevices(Iterator $sel = null)
    {
        $list = new listDevices();
        $arr = DB::getListDevices($sel);
        foreach ($arr as $value) {
            $device = self::createDevice($value);
            if (!is_null($device)) {
                $list->append($device);
            }
        }
        return $list;
    }

    /**
     *  Обновляет set_alarm у 1wire устройств значениями из базы данных
     * @throws Exception
     */
    public static function updateAlarmOWireSensorDeviceFromDB()
    {
        $MAX_CHECK_1WIRE_DIR = 10;  //количество попыток
        $PAUSE_CHECK_1WIRE_DIR = 1; //пауза между попытками
        $subDir = '/alarm'; //поверяем что есть этот каталог

        //Т.к. взаимодействие с 1wire идет через файловую систему сначала ждем пока будут доступен
        //соответствующий каталог
        $is1wire = false;
        for ($n = 0; $n<$MAX_CHECK_1WIRE_DIR; $n++) {
            $is1wire = self::check1WireDir($subDir);
            if ($is1wire) break;
            sleep($PAUSE_CHECK_1WIRE_DIR);
        }
        if (!$is1wire) {
            logger::writeLog('При попытки обновить set_alarm не найден путь до каталога 1wire', loggerTypeMessage::WARNING ,loggerName::ACCESS);
            return;
        }
        $sel = new selectOption();
        $sel->set('Disabled', 0);
        $sel->set('NetTypeID', netDevice::ONE_WIRE);
        $sel->set('DeviceTypeID', typeDevice::KEY_IN);
        $sel->set('OW_is_alarm', 1);
        $listDeviceSensor1Wire = self::getListDevices($sel);
        foreach ($listDeviceSensor1Wire as $device) {
            $devicePhysic = $device->getDevicePhysic();
            if (is_a($devicePhysic, 'iDeviceSensorPhysicOWire')) {
                $devicePhysic->updateAlarm();
            }
        }
    }

    /** Проверка существования каталога для 1wire устройств
     * @param string $subDir - подкаталог для проверки, значение по умолчанию пустая строка
     * @return bool
     */
    public static function check1WireDir($subDir = '') {
        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);
        if (!is_dir($OWNetDir)) {
            return false;
        }
        $fullPath = $OWNetDir.$subDir;
        return is_dir($fullPath);
    }

    public static function getDevicePhysicData($idDevice) {
        $deviceData = new deviceData($idDevice);
        $data = $deviceData->getData();
        return $data->getDataJSON();
    }

    public static function updateTestCode($device, $code, $updateTime = null) {
        if (is_null($updateTime)) { $updateTime = time(); }
        if (is_a($device, 'aDevice') && is_numeric($code)) {
            DB::updateTestDeviceCode($device, $code, $updateTime);
        }
    }

    public static function getLastAvailability(iDevice $device) {
        if (is_a($device, 'aDevice')) {
            return DB::lastDeviceAvailability($device);
        }
        return null;
    }

    public static function getLastTestCode() {
        $arrValue = DB::getLastTestCode();
        $arrKey = array_column($arrValue, 'DeviceID', 'DeviceID');
        return array_combine($arrKey, $arrValue);
    }

    public static function checkDataValue($nameValue, $arr)
    {
        if (is_array($arr)) {
            return array_key_exists($nameValue, $arr) ? $arr[$nameValue] : null;
        } else {
            return null;
        }
    }

}

class managerAlarmDevice {

    public static function createAlarm($topic, $devicePhysic) {
        if (!is_object($devicePhysic)) {
            return null;
        }
        switch (get_class($devicePhysic)) {
            case 'gasSensorMQQTPhysic' :
                $className = 'gasSensorAlarmMQQT';
                break;
            default :
                return null;
        }

        if (class_exists($className)) {
            return new $className($topic);
        }
        else {
            return null;
        }
    }

}