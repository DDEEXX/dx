<?php
require_once(dirname(__FILE__) . '/AliseFormat.class.php');

class AliceFormatter {

    static private function createBrightness($format) {
        switch ($format) {
            case 0 : return new  _AliseFormatBrightness_0;
            default : return null;
        }
    }

    static private function createOn($format) {
        switch ($format) {
            case 0 : return new  _AliseFormatOn_0;
            case 1 : return new  _AliseFormatOn_1;
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
}