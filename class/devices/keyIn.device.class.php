<?php
/** Входящий ключ
 * "Сухой контакт", датчик движения, датчик пересечения. Принимает два значения 0 и 1
 * опрос 1wire датчика через Read Conditional Search ROM, поэтому для этой сети device - "пустой"
 */

class keyInSensorPhysicMQQT extends aDeviceSensorPhysicMQTT
{
    public function __construct($topic)
    {
        parent::__construct($topic, 'status', formatValueDevice::MQTT_KEY_IN);
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

    function requestData()
    {

    }
}

class keyInSensorFactory
{
    static public function create($net, $address, $ow_alarm, $topicCmnd)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new keyInSensorPhysicMQQT($topicCmnd);
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
        $topicCmnd = $options['topic_cmnd'];
        $this->devicePhysic = keyInSensorFactory::create($this->getNet(), $address, $ow_alarm, $topicCmnd);
    }

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }

}
