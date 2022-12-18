<?php
/** Выходящий ключ
 * Силовой ключ, реле, коммутация низких токов и т.д.
  */

class KeyOutOWire extends aDeviceMakerPhysicOWire
{

    /**
     * @param $address - 1wire address
     */
    public function __construct($address)
    {
        parent::__construct($address);
    }


    function getStatus()
    {
        // TODO: Implement getStatus() method.
    }

    function setData($data)
    {
        // TODO: Implement setData() method.
    }

    function test()
    {
        // TODO: Implement test() method.
    }
}

class KeyOutMQQT extends aDeviceMakerPhysicMQTT
{
    public function __construct($topicCmnd, $topicStat)
    {
        parent::__construct($topicCmnd, $topicStat, formatValueDevice::MQTT_KEY_OUT);
    }

    function getStatus()
    {
        // TODO: Implement getStatus() method.
    }

    private function checkValue($nameValue, $arr) {
        if (is_array($arr)) {
            return array_key_exists($nameValue, $arr)?$arr[$nameValue]:null;
        } else {
            return null;
        }
    }

    function setData($data)
    {
        $dataDecode = json_decode($data, true);
        $value = $this->checkValue('value', $dataDecode);
        $status = $this->checkValue('status', $dataDecode);
        $timePause = $this->checkValue('pause', $dataDecode);
        if (!is_null($value)) {
            $payload = $value;
            if (!is_null($status)) {
                $payload = $payload . MQTT_CODE_SEPARATOR. $status;
                if (is_int($timePause)) {
                    $payload = $payload . MQTT_CODE_SEPARATOR. $status;
                }
            }
            parent::setData($payload);
        } else {
            return false;
        }
    }

}

class KeyOutFactory
{
    static public function create($net, $address, $topicCmnd, $topicStat)
    {
        switch ($net) {
            case netDevice::ETHERNET_MQTT:
                return new KeyOutMQQT($topicCmnd, $topicStat);
            case netDevice::ONE_WIRE:
                return new KeyOutOWire($address);
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
        $topicCmnd = $options['topic_cmnd'];
        $topicStat = $options['topic_stat'];
        $this->devicePhysic = KeyOutFactory::create($this->getNet(), $address, $topicCmnd, $topicStat);
    }

    function getStatus()
    {
        return $this->devicePhysic->getStatus();
    }

    function setData($data)
    {
        $this->devicePhysic->setData($data);
    }

}
