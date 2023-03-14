<?php

class kitchenHood_MQTT extends aDeviceMakerPhysicMQTT
{

    public function __construct($mqttParameters)
    {
        parent::__construct($mqttParameters, formatValueDevice::MQTT_KITCHEN_HOOD);
    }

}

class kitchenHood extends aSensorDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::KITCHEN_HOOD);
        $mqttParameters = ['topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicTest' => $options['topic_test']];
        $this->devicePhysic = new kitchenHood_MQTT($mqttParameters);
    }

    public function saveValue($value) {

        $dateValue = date('Y-m-d H:i:s');
        $deviceID = $this->getDeviceID();

        $query = sprintf('INSERT INTO tdevicevalue (ValueID, DeviceID, Date, Value) VALUES (NULL, \'%s\', \'%s\', \'%s\')',
            $deviceID, $dateValue, $value);

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

    function requestData()
    {
        if ($this->devicePhysic instanceof aDeviceSensorPhysic) {
            $this->devicePhysic->requestData();
        }
    }
}