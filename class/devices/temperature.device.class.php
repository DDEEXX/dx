<?php
/** Датчик температуры
 */

const CODE_NO_TEMP = -1000;

class temperatureSensor1Wire extends aDeviceSensorPhysicOWire
{
    /**
     * @param $address - 1wire address
     * @param $alarm
     */
    public function __construct($address, $alarm)
    {
        parent::__construct($address, $alarm);
        //$this->value = managerValues::createDeviceValue();
    }

    function requestData()
    {
        $value = CODE_NO_TEMP;
        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);
        $address = $this->getAddress();
        if (preg_match('/^28\.[A-F0-9]{12,}/', $address)) { //это датчик DS18B20
            $filename = $OWNetDir . '/' . $address . '/temperature12';
            if (file_exists($filename)) {
                $f = file($filename);
                if ($f === false) { //попробуем еще раз
                    usleep(100000); //ждем 0.1 секунд
                    $f = file($filename);
                }
                if ($f === false) {
                    logger::writeLog('Ошибка получения температуры с датчика :: ' . $address, loggerTypeMessage::ERROR);
                } else {
                    $value = $f[0];
                }
            }
        } else {
            logger::writeLog('Неудачная попытка получить данные с датчика :: ' . $address, loggerTypeMessage::ERROR);
        }
        return $value;
    }

    function test()
    {
        $result = testDeviceCode::NO_CONNECTION;
        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);
        $address = $this->getAddress();
        if (preg_match('/^28\.[A-F0-9]{12,}/', $address)) { //это датчик OWire
            $filename = $OWNetDir . '/' . $address . '/temperature12';
            if (file_exists($filename)) {
                $f = file($filename);
                if ($f === false) { //попробуем еще раз
                    usleep(50000); //ждем 0.05 секунд
                    $f = file($filename);
                }
                if ($f !== false) {
                    $result = testDeviceCode::WORKING;
                }
            }
        } else {
            $result = testDeviceCode::ONE_WIRE_ADDRESS;
        }
        return $result;
    }
}

class temperatureSensorMQQTPhysic extends aDeviceSensorPhysicMQTT
{
    const DEFAULT_PAYLOAD = 'temperature';

    public function __construct($mqttParameters, $valueFormat)
    {
        if (empty($mqttParameters['payload'])) {
            $mqttParameters['payload'] = self::DEFAULT_PAYLOAD;
        }
        $this->value = temperatureValuesFactory::createDeviceValue($valueFormat);
        parent::__construct($mqttParameters, formatValueDevice::MQTT_TEMPERATURE);
    }
}

class deviceTemperatureValueSM extends aDeviceValueSM
{
    function setValue($value, $idDevice)
    {
        // TODO: Implement setValue() method.
    }

    function printValue($idDevice)
    {
        // TODO: Implement toString() method.
    }
}

class deviceTemperatureValueDB extends aDeviceValueDB
{
    function setValue($value, $idDevice)
    {
        $dateValue = date('Y-m-d H:i:s');
        $currentData = $this->getValue($idDevice);
        $insertData = !is_array($currentData);

        if ($insertData) {
            $query = sprintf('INSERT INTO tdevicevalue (DeviceID, Date, Value) VALUES (\'%s\', \'%s\', \'%s\')',
                $idDevice, $dateValue, $value);
        } else {
            $query = sprintf('UPDATE tdevicevalue SET Date = \'%s\', Value = \'%s\' WHERE DeviceID = %s',
                $dateValue, $value, $idDevice);
        }

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::execute($con, $query);
            if (!$result) {
                logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                    loggerTypeMessage::ERROR, loggerName::ERROR);
            }
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при добавлении данных в базу данных',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

        unset($con);
    }

    function printValue($idDevice)
    {
        $valueData = $this->getValue($idDevice);
        if (!is_array($valueData)) return null;

        $result = [];
        $result['date'] = $valueData['date'];
        $arValueData = json_decode($valueData['value'], true) ;
        $result['value'] = $arValueData['temperature'];
        return $result;
    }
}

class temperatureSensorFactory
{
    static public function create($net, $address, $alarm, $mqttParameters, $valueFormat)
    {
        switch ($net) {
            case netDevice::ONE_WIRE:
                return new temperatureSensor1Wire($address, $alarm);
            case netDevice::ETHERNET_MQTT:
                return new temperatureSensorMQQTPhysic($mqttParameters, $valueFormat);
            default :
                return new DeviceSensorPhysicDefault();
        }
    }
}

class  temperatureValuesFactory
{
    public static function createDeviceValue($shared = 0) {
        switch ($shared) {
            case 0 : return new deviceTemperatureValueSM();
            case 1 : return new deviceTemperatureValueDB();
            default :
                logger::writeLog('Ошибка при создании объекта deviceValue (managerValues.class.php). $shared = '.$shared,
                    loggerTypeMessage::ERROR, loggerName::ERROR);
        }
        return null;
    }
}

class temperatureSensorDevice extends aSensorDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::TEMPERATURE);
        $address = $options['Address'];
        $ow_alarm = $options['OW_alarm'];
        $valueFormat = $options['value_format'];
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm'],
            'payload' => $options['payload_cmnd']];
        $this->devicePhysic = temperatureSensorFactory::create($this->getNet(), $address, $ow_alarm, $mqttParameters, $valueFormat);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $value = $this->devicePhysic->requestData();

            if (!is_null($value)) { //запрос вернул результат, запишем в sm

                $dataValue = time();
                if ($value == CODE_NO_TEMP || !is_numeric($value)) {
                    $valueNull = true;
                    $value = 0.0;
                } else {
                    $valueNull = false;
                    $value = round((float)$value, valuePrecision::TEMPERATURE);
                }

                $dataDevice = new deviceData($this->getDeviceID());
                $dataDevice->setData($value, $dataValue, $valueNull);

            }
        }
    }
}
