<?php
/** Датчик температуры
 */

const CODE_NO_TEMP = -1000;

class formatterTemperature1Wire implements iFormatterValue
{
    function formatRawValue(array $value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;
        $valueTemperature = trim($value['value']);
        if ($valueTemperature == CODE_NO_TEMP || !is_numeric($valueTemperature)) {
            $result->valueNull = true;
            $result->value = 0.0;
        } else {
            $result->value = round((float)$valueTemperature, valuePrecision::TEMPERATURE);
        }
        return $result;
    }

    function formatTestCode($value)
    {
        return $value;
    }
}

class formatterTemperatureMQTT_1 implements iFormatterValue
{
    function formatRawValue(array $value)
    {
        $result = new formatDeviceValue();
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

    function formatTestCode($value)
    {
        $arValue = json_decode($value);
        switch ($arValue->state) {
            case 'online' :
                $testCode = testDeviceCode::WORKING;
                break;
            case 'offline' :
                $testCode = testDeviceCode::NO_CONNECTION;
                break;
            default :
                $testCode = testDeviceCode::UNKNOWN;
        }
        return $testCode;
    }
}

class temperatureSensor1Wire extends aDeviceSensorPhysicOWire
{
    public function __construct($parameters, $OWParameters)
    {
        $this->value = valuesFactory::createDeviceValue($parameters, new formatterTemperature1Wire());
        parent::__construct($OWParameters['address'], $OWParameters['ow_alarm']);
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
    private static function getConstructParam($parameters)
    {
        $result = [];
        $result['payloadRequest'] = '';
        $result['selfActivity'] = false;
        $result['formatter'] = null;
        switch ($parameters['valueFormat']) {
            case 0 :
                $result['payloadRequest'] = 'temperature';
                $result['selfActivity'] = false;
                $result['formatter'] = new formatterNumeric();
                break;
            case 1 :
                $result['payloadRequest'] = '{"state": ""}';
                $result['selfActivity'] = true;
                $result['formatter'] = new formatterTemperatureMQTT_1();
                break;
        }
        return $result;
    }

    public function __construct($parameters, $mqttParameters)
    {
        $param = self::getConstructParam($parameters);
        $mqttParameters['payloadRequest'] = $param['payloadRequest'];
        $this->selfActivity = $param['selfActivity'];
        $this->value = valuesFactory::createDeviceValue($parameters, $param['formatter']);
        parent::__construct($mqttParameters, formatValueDevice::MQTT_TEMPERATURE);
    }

    public function formatTestPayload($testPayload, $ignoreUnknown = false)
    {
        if ($this->value instanceof iDeviceValue) {
            $testPayload = $this->value->getFormatTestCode($testPayload); //{"state":"online"}/{"state":"offline"}
        }
        return parent::formatTestPayload($testPayload, $ignoreUnknown);
    }
}

class temperatureSensorFactory
{
    static public function create($parameters, $OWParameters, $mqttParameters)
    {
        switch ($parameters['net']) {
            case netDevice::ONE_WIRE:
                return new temperatureSensor1Wire($parameters, $OWParameters);
            case netDevice::ETHERNET_MQTT:
                return new temperatureSensorMQQTPhysic($parameters, $mqttParameters);
            default :
                return new DeviceSensorPhysicDefault();
        }
    }
}

class temperatureSensorDevice extends aSensorDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::TEMPERATURE);
        $parameters = [
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

            if (!is_null($value)) { //запрос вернул результат
                $this->devicePhysic->setValue($value);
            }
        }
    }
}
