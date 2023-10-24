<?php

class formatterBoilerOpenTerm implements iFormatterValue
{
    function formatRawValue($value)
    {
        $dValue =  json_decode($value);
        $result = new stdClass();
        $result->value = new stdClass();
        $result->value->room = $dValue->room; //температура в комнате
        $result->value->_spr = $dValue->_spr; //целевая температура (установка)
        $result->value->ch = $dValue->ch;     //подача СО
        $result->value->retb = $dValue->retb; //обратка СО
        $result->value->tset = $dValue->tset; //расчетная СО
        $result->value->chon = $dValue->chon; //статус СО

        $result->value->_dhw = $dValue->_dhw; //целевая температура ГВС
        $result->value->dhw = $dValue->dhw;   //текущая температура ГВС
        $result->value->spdhw = $dValue->spdhw;//Принятая ГВС
        $result->value->dhwon = $dValue->dhwon;//статус ГВС

        $result->value->mlev = $dValue->mlev; //уровень модуляции горелки
        $result->value->flon = $dValue->flon; //статус горелки

        return $result;
    }

    function formatTestCode($value)
    {
        $objValue = json_decode($value);
        return isset($objValue->tx) ? testDeviceCode::WORKING : testDeviceCode::UNKNOWN;
    }

    function formatOutData($data)
    {
        return $data;
    }
}

class boilerOpenTherm_MQTT extends aDeviceMakerPhysicMQTT
{
    public function __construct($parameters, $mqttParameters)
    {
        $this->selfState = true;
        $this->value = valuesFactory::createDeviceValue($parameters, new formatterBoilerOpenTerm());
        parent::__construct($parameters['deviceID'], $mqttParameters);
    }
}

class boilerOpenTherm extends aMakerDevice
{
    public function __construct(array $options)
    {
        parent::__construct($options, typeDevice::GAS_SENSOR);
        $parameters = [
            'deviceID' => $this->getDeviceID(),
            'valueStorage'=>$options['value_storage']
        ] ;
        $mqttParameters = [
            'topicCmnd' => $options['topic_cmnd'],
            'topicStat' => $options['topic_stat'],
            'topicAvailability' => '',
            'topicSet' => $options['topic_cmnd'] . '/set',
            'topicTest' => $options['topic_test'],
            'payloadRequest' => $options['payload_cmnd']];
        $this->devicePhysic = new boilerOpenTherm_MQTT($parameters, $mqttParameters);
    }

    function requestData()
    {
    }
}