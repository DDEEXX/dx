<?php
/** Входящий ключ
 * "Сухой контакт", датчик движения, датчик пересечения. Принимает два значения 0 и 1
 * опрос 1wire датчика через Read Conditional Search ROM, поэтому для этой сети device - "пустой"
 */

class formatterKeyIn1Wire extends aFormatterValue
{
    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;
        if (is_int($value))
            $result->value = $value;
        else {
            $result->valueNull = true;
            $result->value = 0.0;
        }
        return $result;
    }
}

class formatterKeyInMQTT_1 extends aFormatterValue
{
    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;
        if (strtoupper($value) === 'ON' || strtoupper($value) === 'TRUE' || $value === true || $value === 1 || $value === '1') $result->value = 1;
        else if (strtoupper($value) === 'OFF' || strtoupper($value) === 'FALSE' || $value === false || $value === 0 || $value === '0') $result->value = 0;
        else {
            $result->value = 0;
            $result->valueNull = true;
        }
        return $result;
    }
}

class formatterKeyInMQTT_2 extends aFormatterValue
{
    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;
        $objValue = json_decode($value);
        if ($objValue->state == 'on') $result->value = 1;
        else if ($objValue->state == 'off') $result->value = 0;
        else {
            $result->value = 0;
            $result->valueNull = true;
        }
        return $result;
    }

    function formatTestCode($value)
    {
        $objValue = json_decode($value);
        switch ($objValue->state) {
            case 'online' :
                $testCode = testDeviceCode::WORKING;
                break;
            case 'offline' :
                $testCode = testDeviceCode::NO_CONNECTION;
                break;
            default :
                $testCode = testDeviceCode::UNKNOWN;
        }
        return parent::formatTestCode($testCode);
    }
}

class keyInSensorPhysicMQQT extends aDeviceSensorPhysicMQTT
{
    private static function getConstructParam($parameters, &$mqttParameters)
    {
        $result = [];
        $result['payloadRequest'] = '';
        $result['selfState'] = false;
        $result['formatter'] = null;
        switch ($parameters['valueFormat']) {
            case 0 :
                $result['selfState'] = false;
                $result['formatter'] = new formatterKeyInMQTT_1();
                break;
            case 1 :
                $mqttParameters['topicAvailability'] = '';
                $result['selfState'] = true;
                $result['formatter'] = new formatterKeyInMQTT_2();
                break;
        }
        return $result;
    }

    public function __construct($parameters, $mqttParameters)
    {
        $param = self::getConstructParam($parameters, $mqttParameters);
        $this->selfState = $param['selfState'];
        $this->value = valuesFactory::createDeviceValue($parameters, $param['formatter']);
        parent::__construct($parameters['deviceID'], $mqttParameters, formatValueDevice::MQTT_KEY_IN);
    }

    public function formatTestPayload($testPayload, $ignoreUnknown = false)
    {
        if ($this->value instanceof iDeviceValue) {
            $testPayload = $this->value->getFormatTestCode($testPayload); //{"state":"online"}/{"state":"offline"}
        }
        return parent::formatTestPayload($testPayload, $ignoreUnknown);
    }

}

class keyInSensorPhysicOWire extends aDeviceSensorPhysicOWire
{
    public function __construct($parameters, $OWParameters)
    {
        $this->value = valuesFactory::createDeviceValue($parameters, new formatterKeyIn1Wire());
        parent::__construct($parameters['deviceID'], $OWParameters['address'], $OWParameters['ow_alarm']);
    }

    function requestData() { }

    function test()
    {
        $result = testDeviceCode::NO_CONNECTION;
        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);
        $address = $this->getAddress();
        if (preg_match('/^12\.[A-F0-9]{12,}/', $address)) { //это датчик OWire
            $fileName = $OWNetDir . '/' . $address . '/set_alarm';
            if (file_exists($fileName)) {
                $f = file($fileName);
                if ($f !== false) {
                    $result = testDeviceCode::WORKING;
                    if (count($f) > 0) {
                        if ($f[0] != $this->getAlarm()) {
                            $result = testDeviceCode::ONE_WIRE_ALARM;
                        }
                    } else {
                        $result = testDeviceCode::ONE_WIRE_ALARM;
                    }
                }
            }
        } else {
            $result = testDeviceCode::ONE_WIRE_ADDRESS;
        }
        return $result;
    }
}

class keyInSensorFactory
{
    static public function create($parameters, $OWParameters, $mqttParameters)
    {
        switch ($parameters['net']) {
            case netDevice::ONE_WIRE:
                return new keyInSensorPhysicOWire($parameters, $OWParameters);
            case netDevice::ETHERNET_MQTT:
                return new keyInSensorPhysicMQQT($parameters, $mqttParameters);
            default :
                return new DeviceSensorPhysicDefault($parameters['deviceID']);
        }
    }
}

class keyInSensorDevice extends aSensorDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KEY_IN);
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

        $this->devicePhysic = keyInSensorFactory::create($parameters, $OWParameters, $mqttParameters);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}
