<?php
/** Выходящий ключ
 * Силовой ключ, реле, коммутация низких токов и т.д.
 */

function convertStatus($status)
{
    if (!is_string($status)) {
        return 0;
    }
    if (array_key_exists($status, statusKeyData::status)) {
        return statusKeyData::status[$status];
    }
    return 0;
}


class formatterKeyOut1Wire_3A extends aFormatterValue
{
    function formatRawValue($value)
    {
        return $value;
    }

    function formatOutData($data)
    {
        $dataDecode = json_decode(parent::formatOutData($data), true);
        if (is_null($dataDecode)) return null;
        if (!isset($dataDecode['value'])) return null;
        $value = 'off';
        if (is_numeric($dataDecode['value'])) $value = (int)$dataDecode['value'] > 0 ? 'on' : 'off';
        elseif (strtolower($dataDecode['value']) == 'on') $value = 'on';
        elseif (strtolower($dataDecode['value']) == 'pulse') $value = 'pulse';
        $result['value'] = $value;

        if (isset($dataDecode['status'])) $result['status'] = strtolower($dataDecode['status']);

        return $result;
    }
}

class makerKeyOut1Wire_3A implements iMaker
{
    function make($data)
    {
        $ow = new OWNet($data['OWNetAddress']);
        $address = $data['address'];
        $channel = $data['channel'];
        $value = false;
        if (strtolower($data['value']) == 'pulse') {
            $ow->set('/uncached/' . $address . '/PIO.' . $channel, 1);
            usleep(500000);
            $ow->set('/uncached/' . $address . '/PIO.' . $channel, 0);
        } else {
            $value = $data['value'] == 'on' ? 1 : 0;
            if (!$ow->set('/uncached/' . $address . '/PIO.' . $channel, $value)) $value = false;
        }
        unset($ow);
        return $value;
    }
}

class formatterKeyOutMQTT_1 extends aFormatterValue
{
    const MQTT_CODE_SEPARATOR = ';';

    function formatRawValue($value)
    {
        $result = new formatDeviceValue();
        $result->valueNull = false;
        $result->status = 0;

        $value_ = null;
        $status = null;
        if (is_string($value)) { //может прийти команда и статус
            $p = explode(self::MQTT_CODE_SEPARATOR, $value);
            if (strtoupper($p[0]) == 'OFF' || strtoupper($p[0]) == 'FALSE' || $p[0] == '0') {
                $value_ = 0;
            } elseif (strtoupper($p[0]) == 'ON' || strtoupper($p[0]) == 'TRUE' || $p[0] == '1') {
                $value_ = 1;
            }
            if (count($p) > 1) {
                $status = $p[1];
            }
        } elseif (is_int($value)) {
            $value_ = $value == 0 ? 0 : 1;
        } elseif (is_bool($value)) {
            $value_ = $value ? 1 : 0;
        }
        if (!is_null($value_)) {
            $result->value = $value_;
            if (!is_null($status)) {
                $result['status'] = convertStatus($status);
            }
        } else $result->valueNull = true;
        return $result;
    }

    function formatOutData($data)
    {
        $dataDecode = json_decode(parent::formatOutData($data), true);
        if (is_null($dataDecode)) return null;
        if (!isset($dataDecode['value'])) return null;
        $value = 'off';
        if (is_numeric($dataDecode['value'])) $value = (int)$dataDecode['value'] > 0 ? 'on' : 'off';
        elseif (strtolower($dataDecode['value']) == 'on') $value = 'on';
        elseif (strtolower($dataDecode['value']) == 'pulse') $value = 'pulse';
        $result['value'] = $value;

        if (isset($dataDecode['status'])) $result['status'] = strtolower($dataDecode['status']);
        if (isset($dataDecode['pause'])) $result['pause'] = strtolower($dataDecode['pause']);

        return $result;
    }
}

class makerKeyOutMQTT_1 implements iMaker
{
    const MQTT_CODE_SEPARATOR = ';';

    function make($data)
    {
        $payload = '';
        if (isset($data['value']) && is_string($data['value'])) $payload=$payload.$data['value'];
        else return null;
        if (isset($data['status'])) {
            $payload=$payload.self::MQTT_CODE_SEPARATOR;
            if (is_string($data['status'])) $payload=$payload.$data['status'];
            if (isset($data['pause'])) {
                $payload=$payload.self::MQTT_CODE_SEPARATOR;
                if (is_numeric($data['pause'])) $payload=$payload.$data['pause'];
            }
        }
        return $payload;
    }
}

class formatterKeyOutMQTT_2 extends aFormatterValue
{
    function formatRawValue($value)
    {
        logger::writeLog('пришел ' .$value, loggerTypeMessage::NOTICE, loggerName::ERROR);

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
        if (isset($objValue->status)) $result->status = convertStatus($objValue->status);
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

    function formatOutData($data)
    {
        $dataDecode = json_decode(parent::formatOutData($data), true);
        if (is_null($dataDecode)) return null;
        if (!isset($dataDecode['value'])) return null;
        $value = 'off';
        if (is_numeric($dataDecode['value'])) $value = (int)$dataDecode['value'] > 0 ? 'on' : 'off';
        elseif (strtolower($dataDecode['value']) == 'on') $value = 'on';
        $result['value'] = $value;

        if (isset($dataDecode['status'])) $result['status'] = strtolower($dataDecode['status']);

        return $result;
    }
}

class makerKeyOutMQTT_2 implements iMaker
{
    function make($data)
    {
        $payload = [];
        if (isset($data['value']) && is_string($data['value'])) $payload['state'] = $data['value'];
        if (isset($data['status']) && is_string($data['status'])) $payload['status'] = $data['status'];
        return json_encode($payload);
    }
}

function checkKeyOutDataValue($nameValue, $arr)
{
    if (is_array($arr)) {
        return array_key_exists($nameValue, $arr) ? $arr[$nameValue] : null;
    } else {
        return null;
    }
}

class KeyOutOWire extends aDeviceMakerPhysicOWire
{
    private static function getConstructParam($parameters, $OWParameters)
    {
        $result = [];
        $result['formatter'] = null;
        $result['maker'] = null;
        if (preg_match('/^3A\.[A-F0-9]{12,}/', $OWParameters['address'])) { //DS2413
            $result['formatter'] = new formatterKeyOut1Wire_3A();
            $result['maker'] = new makerKeyOut1Wire_3A();
        }
        return $result;
    }

    public function __construct($parameters, $OWParameters)
    {
        $param = self::getConstructParam($parameters, $OWParameters);
        $this->value = valuesFactory::createDeviceValue($parameters, $param['formatter']);
        $this->maker = $param['maker'];
        parent::__construct($parameters['deviceID'], $OWParameters['address'], $OWParameters['chanel']);
    }

    function setData($data)
    {
        $makeData = $this->value->getFormatOutData($data);
        if (is_null($makeData)) return;
        $makeData['OWNetAddress'] = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        $makeData['channel'] = $this->getChanel();
        $makeData['address'] = $this->getAddress();

        $result = $this->maker->make($makeData);
        if ($result === false) return;

        //если после установки значения вернулся результат выполнения, запишем состояние
        $dataValue = time();
        if (isset($makeData['status'])) $status = convertStatus($makeData['status']);
        else $status = 0;
        $dataDevice = new deviceData($this->getDeviceID());
        $dataDevice->setData($result, $dataValue, false, $status);
    }

    function test()
    {
        $result = testDeviceCode::NO_CONNECTION;
        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);
        $channel = $this->getChanel();
        $address = $this->getAddress();
        if (preg_match('/^3A\.[A-F0-9]{12,}/', $address)) { //это датчик OWire
            $fileName = $OWNetDir . '/' . $address . '/PIO.' . $channel;
            if (file_exists($fileName)) {
                $f = file($fileName);
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

class KeyOutMQQT extends aDeviceMakerPhysicMQTT
{

    private static function getConstructParam($parameters, &$mqttParameters)
    {
        $result = [];
        $result['payloadRequest'] = '';
        $result['selfState'] = false;
        $result['formatter'] = null;
        $result['maker'] = null;
        switch ($parameters['valueFormat']) {
            case 0 :
                $result['selfState'] = false;
                $result['formatter'] = new formatterKeyOutMQTT_1();
                $result['maker'] = new makerKeyOutMQTT_1();
                break;
            case 1 :
                $mqttParameters['topicAvailability'] = '';
                $result['selfState'] = true;
                $result['formatter'] = new formatterKeyOutMQTT_2();
                $result['maker'] = new makerKeyOutMQTT_2();
                break;
        }
        return $result;
    }

    public function __construct($parameters, $mqttParameters)
    {
        $param = self::getConstructParam($parameters, $mqttParameters);
        $this->selfState = $param['selfState'];
        $this->maker = $param['maker'];
        $this->value = valuesFactory::createDeviceValue($parameters, $param['formatter']);
        parent::__construct($parameters['deviceID'], $mqttParameters, formatValueDevice::MQTT_KEY_OUT);
    }

    function setData($data)
    {
        $makeData = $this->value->getFormatOutData($data);
        if (is_null($makeData)) return;
        $data = $this->maker->make($makeData);
        parent::setData($data);
    }
}

class KeyOutFactory
{
    static public function create($parameters, $OWParameters, $mqttParameters)
    {
        switch ($parameters['net']) {
            case netDevice::ETHERNET_MQTT:
                return new KeyOutMQQT($parameters, $mqttParameters);
            case netDevice::ONE_WIRE:
                return new KeyOutOWire($parameters, $OWParameters);
            default :
                return new DeviceMakerPhysicDefault($parameters['deviceID']);
        }
    }
}

class KeyOutMakerDevice extends aMakerDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KEY_OUT);
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
            'topicAlarm' => $options['topic_alarm']];
        $OWParameters = [
            'address' => $options['Address'],
            'chanel' => $options['OW_Chanel']];
        $this->devicePhysic = KeyOutFactory::create($parameters, $OWParameters, $mqttParameters);
    }
}
