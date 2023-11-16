<?php
require_once(dirname(__FILE__) . '/AliseFormat.class.php');

class AliceFormatter {

    static private function createBrightness($format) {
        switch ($format) {
            case 1 : return new  _AliseFormatBrightness_1;
            default : return null;
        }
    }

    static private function createOn($format) {
        switch ($format) {
            case 1 : return new  _AliseFormatOn_1;
            case 2 : return new  _AliseFormatOn_2;
            case 3 : return new  _AliseFormatOn_3;
            case 4 : return new  _AliseFormatOn_4;
            default : return null;
        }
    }

    static private function createBrightnessStat($format) {
        switch ($format) {
            case 1 : return new  _AliseFormatBrightnessStat_1;
            default : return null;
        }
    }

    static private function createOnStat($format) {
        switch ($format) {
            case 1 : return new  _AliseFormatOnStat_1;
            case 2 : return new  _AliseFormatOnStat_2;
            default : return null;
        }
    }

    static private function createSet($instance, $format) {
        switch ($instance) {
            case 'on' : return self::createOn($format);
            case 'brightness' : return self::createBrightness($format);
            default : return null;
        }
    }

    static private function createStat($instance, $format) {
        switch ($instance) {
            case 'on' : return self::createOnStat($format);
            case 'brightness' : return self::createBrightnessStat($format);
            default : return null;
        }
    }

    static public function create($instance, $typeTopic, $format) {
        if ($typeTopic == typeTopic::SET) {
            $formatter = self::createSet($instance, $format);
        } elseif ($typeTopic == typeTopic::STATUS) {
            $formatter = self::createStat($instance, $format);
        } else $formatter = null;
        return $formatter;
    }
}

class Alice
{
    private $type;
    public $mqtt = [];

    public function __construct($optionsJSON)
    {
        $options = json_decode($optionsJSON);
        if (is_null($options)) {
            logger::writeLog('Неверно заданы параметры для Алисы',
                loggerTypeMessage::FATAL, loggerName::ERROR);
            return;
        }
        $this->type = $options->type;

        foreach ($options->mqtt as $value) {
            $data = new stdClass();
            $data->instance = $value->type;
            $data->typeTopic = typeTopic::SET;
            $data->topic = $value->set;
            $data->formater = AliceFormatter::create($data->instance, $data->typeTopic, $value->formatSet);
            $this->mqtt[] = $data;

            $data = new stdClass();
            $data->instance = $value->type;
            $data->typeTopic = typeTopic::STATUS;
            $data->topic = $value->stat;
            $data->formater = AliceFormatter::create($data->instance, $data->typeTopic, $value->formatStat);
            $this->mqtt[] = $data;
        }
    }

    public function sentStatus($payload) {
        foreach ($this->mqtt as $mqtt) {
            if ($mqtt->typeTopic != typeTopic::STATUS) continue;
            if (is_null( $mqtt->formater)) continue;
            $formatValue = $mqtt->formater->convert($payload);
            if (is_null($formatValue)) continue;
            mqttPublish::publish($mqtt->topic, $formatValue);
        }
    }
}