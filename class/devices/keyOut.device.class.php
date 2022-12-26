<?php
/** Выходящий ключ
 * Силовой ключ, реле, коммутация низких токов и т.д.
 */

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

    /**
     * @param $address - 1wire address
     */
    public function __construct($address, $chanel)
    {
        parent::__construct($address, $chanel);
    }

    function setData($data)
    {
        $dataDecode = json_decode($data, true);
        if (is_null($dataDecode)) {
            return false;
        }
        $value = checkKeyOutDataValue('value', $dataDecode);
        $channel = $this->getChanel();
        $address = $this->getAddress();

        $result = false;
        $OWNetAddress = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_ADDRESS);
        if (preg_match('/^3A\.[A-F0-9]{12,}/', $address)) { //DS2413
            $ow = new OWNet($OWNetAddress);
            $result = $ow->set('/uncached/' . $address . '/PIO.' . $channel, $value);
            unset($ow);
        } else {
            logger::writeLog('Неудачная попытка записать значение в датчик :: ' . $address, loggerTypeMessage::ERROR, loggerName::ERROR);
        }
        return $result;
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
    public function __construct($topicCmnd, $topicStat, $topicTest)
    {
        parent::__construct($topicCmnd, $topicStat, $topicTest, formatValueDevice::MQTT_KEY_OUT);
    }

    function setData($data)
    {
        $dataDecode = json_decode($data, true);
        if (is_null($dataDecode)) {
            return false;
        }
        $value = checkKeyOutDataValue('value', $dataDecode);
        $status = checkKeyOutDataValue('status', $dataDecode);
        $timePause = checkKeyOutDataValue('pause', $dataDecode);
        if (!is_null($value)) {
            $payload = $value;
            if (!is_null($status)) {
                $payload = $payload . MQTT_CODE_SEPARATOR . $status;
                if (is_int($timePause)) {
                    $payload = $payload . MQTT_CODE_SEPARATOR . $timePause;
                }
            }
            return parent::setData($payload);
        } else {
            return false;
        }

    }
}

class KeyOutFactory
{
    static public function create($net, $address, $chanel, $topicCmnd, $topicStat, $topicTest)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new KeyOutMQQT($topicCmnd, $topicStat, $topicTest);
            case netDevice::ONE_WIRE:
                return new KeyOutOWire($address, $chanel);
            default :
                return new DeviceMakerPhysicDefault();
        }
    }
}

class KeyOutMakerDevice extends aMakerDevice
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KEY_OUT);
        $address = $options['Address'];
        $chanel = $options['OW_Chanel'];
        $topicCmnd = $options['topic_cmnd'];
        $topicStat = $options['topic_stat'];
        $topicTest = $options['topic_test'];
        $this->devicePhysic = KeyOutFactory::create($this->getNet(), $address, $chanel, $topicCmnd, $topicStat, $topicTest);
    }

    private function convertStatus($status)
    {
        if (!is_string($status)) {
            return 0;
        }
        if (array_key_exists($status, statusKeyData::status)) {
            return statusKeyData::status[$status];
        }
        return 0;
    }

    public function setData($data)
    {

        $result = parent::setData($data);
        if ($result) {
            $devicePhysic = $this->getDevicePhysic();
            if ($devicePhysic instanceof KeyOutOWire) {
                $dataDecode = json_decode($data, true);
                if (!is_null($dataDecode)) {
                    $dataValue = time();
                    $value = checkKeyOutDataValue('value', $dataDecode);
                    $status = checkKeyOutDataValue('status', $dataDecode);
                    if (!is_null($status)) {
                        $status = $this->convertStatus($status);
                        if (is_null($status)) $status = 0;
                    }
                    $dataDevice = new deviceData($this->getDeviceID());
                    $dataDevice->setData($value, $dataValue, false, $status);
                }
            }
        }
    }

}
