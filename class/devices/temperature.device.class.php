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

    public function __construct($mqttParameters)
    {
        if (empty($mqttParameters['payload'])) {
            $mqttParameters['payload'] = self::DEFAULT_PAYLOAD;
        }
        parent::__construct($mqttParameters, formatValueDevice::MQTT_TEMPERATURE);
    }
}

class temperatureSensorFactory
{
    static public function create($net, $address, $alarm, $mqttParameters)
    {
        switch ($net) {
            case netDevice::ONE_WIRE:
                return new temperatureSensor1Wire($address, $alarm);
            case netDevice::ETHERNET_MQTT:
                return new temperatureSensorMQQTPhysic($mqttParameters);
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
        $address = $options['Address'];
        $ow_alarm = $options['OW_alarm'];
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test'],
            'topicAlarm' => $options['topic_alarm'],
            'payload' => $options['payload_cmnd']];
        $this->devicePhysic = temperatureSensorFactory::create($this->getNet(), $address, $ow_alarm, $mqttParameters);
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
