<?php
/** Входящий ключ
 * "Сухой контакт", датчик движения, датчик пересечения. Принимает два значения 0 и 1
 * опрос 1wire датчика через Read Conditional Search ROM, поэтому для этой сети device - "пустой"
 */

class keyInSensorPhysicMQQT extends aDeviceSensorPhysicMQTT
{

    const DEFAULT_PAYLOAD = 'status';

    public function __construct($mqttParameters)
    {
        if (empty($mqttParameters['payload'])) {
            $mqttParameters['payload'] = self::DEFAULT_PAYLOAD;
        }
        parent::__construct($mqttParameters, formatValueDevice::MQTT_KEY_IN);
    }
}

class keyInSensorPhysicOWire extends aDeviceSensorPhysicOWire {

    /**
     * @param $address - 1wire address
     * @param $alarm
     */
    public function __construct($address, $alarm)
    {
        parent::__construct($address, $alarm);
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
    static public function create($net, $address, $ow_alarm, $mqttParameters)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new keyInSensorPhysicMQQT($mqttParameters);
            case netDevice::ONE_WIRE:
                return new keyInSensorPhysicOWire($address, $ow_alarm);
            default :
                return new DeviceSensorPhysicDefault();
        }
    }
}

class keyInSensorDevice extends aSensorDevice
{

    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KEY_IN);
        $address = $options['Address'];
        $ow_alarm = $options['OW_alarm'];
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm'],
            'payload' => $options['payload_cmnd']];
        $this->devicePhysic = keyInSensorFactory::create($this->getNet(), $address, $ow_alarm, $mqttParameters);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }

}
