<?php

class kitchenHood_MQTT extends aDeviceSensorPhysicMQTT
{

    public function __construct($mqttParameters)
    {
        parent::__construct($mqttParameters, formatValueDevice::MQTT_KITCHEN_HOOD);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    function getData($deviceID) {

        try {
            $con = sqlDataBase::Connect();
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных в функции DB::getConst. ' . $e->getMessage(),
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return '';
        }
        $deviceID = $con->getConnect()->real_escape_string($deviceID);
        $query = 'SELECT * FROM tdevicevalue WHERE DeviceID=' .$deviceID.' Order By Date Desc LIMIT 1';
        $result = [];
        try {
            $value = queryDataBase::getOne($con, $query);
            if (is_array($value) && array_key_exists('Value', $value)) {
                $result['date'] = strtotime($value['Date']);
                $result['value'] = $value['Value'];
            } else {
                $result['date'] = 0; $result['value'] = '';
            }
        } catch (querySelectDBException $e) {
            $result['date'] = 0; $result['value'] = '';
        }
        return $result;
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

        if (parent::getData() == '') {
            $query = sprintf('INSERT INTO tdevicevalue (DeviceID, Date, Value) VALUES (\'%s\', \'%s\', \'%s\')',
                $deviceID, $dateValue, $value);
        } else {
            $template = 'UPDATE tdevicevalue SET Date = \'%s\', Value = \'%s\' WHERE DeviceID = %s';
            $query = sprintf($template, $dateValue, $value, $deviceID);
        }

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