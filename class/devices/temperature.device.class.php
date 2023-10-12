<?php
/** Датчик температуры
 */

const CODE_NO_TEMP = -1000;

class formatter1Wire implements iFormatterValue
{
    function formatRawValue($value)
    {
        // TODO: Implement formatRawValue() method.
    }
}

class formatterMQTT_1 implements iFormatterValue
{
    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->date = $value['date'];
        $result->valueNull = false;
        $result->status = 0;
        $valueTemperature = trim($value['value']);
        if (is_numeric($valueTemperature))
            $result->value = (float)$valueTemperature;
        else {
            $result->value = '';
            $result->valueNull = true;
        }
        return $result;
    }
}

class formatterMQTT_2 implements iFormatterValue
{
    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->date = $value['date'];
        $result->valueNull = false;
        $result->status = 0;
        $arValueData = json_decode($value['value'], true);
        if ($arValueData['enable_sensor'] && is_numeric($arValueData['temperature']))
            $result->value = $arValueData['temperature'];
        else {
            $result->value = '';
            $result->valueNull = true;
        }
        return $result;
    }
}

class temperatureSensor1Wire extends aDeviceSensorPhysicOWire
{
    /**
     * @param $OWParameters
     */
    public function __construct($OWParameters)
    {
        parent::__construct($OWParameters['address'], $OWParameters['ow_alarm']);
        //$this->value = temperatureValuesFactory::createDeviceValue($id, $valueFormat, $param['formatter']);
    }

    function requestData($ignoreActivity = true)
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
    private static function getConstructParam($parameters) {
        $result = [];
        $result['payloadRequest'] = '';
        $result['selfActivity'] = false;
        $result['formatter'] = null;
        switch ($parameters['valueFormat']) {
            case 0 :
                $result['payloadRequest'] = 'temperature';
                $result['selfActivity'] = false;
                $result['formatter'] = new formatterMQTT_1();
                break;
            case 1 :
                $result['payloadRequest'] = '{"state": ""}';
                $result['selfActivity'] = true;
                $result['formatter'] = new formatterMQTT_2();
                break;
        }
        return $result;
    }

    public function __construct($parameters, $mqttParameters)
    {
        $param = self::getConstructParam($parameters);
        $mqttParameters['payloadRequest'] = $param['payloadRequest'];
        $this->selfActivity = $param['selfActivity'];
        $this->value = temperatureValuesFactory::createDeviceValue($parameters, $param['formatter']);
        parent::__construct($mqttParameters, formatValueDevice::MQTT_TEMPERATURE);
    }
}

class deviceTemperatureValueSM extends aDeviceValueSM
{
    function getFormatValue()
    {
        return new formatDeviceValue(); // TODO: Implement getFormatValue() method.
    }
}

class deviceTemperatureValueDB extends aDeviceValueDB
{
    public function __construct($id, $formatter)
    {
        parent::__construct($id, $formatter);
    }
}

class temperatureSensorFactory
{
    static public function create($parameters, $OWParameters, $mqttParameters)
    {
        switch ($parameters['net']) {
            case netDevice::ONE_WIRE:
                return new temperatureSensor1Wire($OWParameters);
            case netDevice::ETHERNET_MQTT:
                return new temperatureSensorMQQTPhysic($parameters, $mqttParameters);
            default :
                return new DeviceSensorPhysicDefault();
        }
    }
}

class  temperatureValuesFactory
{
    public static function createDeviceValue($parameters, $formatter)
    {
        switch ($parameters['valueStorage']) { //место хранение данных
            case 0 :
                return null; //TODO - new deviceTemperatureValueSM();
            case 1 :
                return new deviceTemperatureValueDB($parameters['deviceID'], $formatter);
            default :
                logger::writeLog('Ошибка при создании объекта deviceValue (managerValues.class.php). $parameters[valueStorage] = ' . $parameters['valueStorage'],
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
        $parameters =[
            'deviceID' => $this->getDeviceID(),
            'net' => $this->getNet(),
            'valueFormat' => $options['value_format'],
            'valueStorage' => $options['value_storage']
        ];
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm'],
            'payloadRequest' => $options['payload_cmnd']];
        $OWParameters = [
            'address' => $options['Address'],
            'ow_alarm' => $options['OW_alarm']];
        $this->devicePhysic = temperatureSensorFactory::create($parameters, $OWParameters, $mqttParameters);
    }

    function requestData($ignoreActivity = true)
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $value = $this->devicePhysic->requestData($ignoreActivity);

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
