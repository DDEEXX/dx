<?php

namespace units2;

interface iUnit
{
    function getId();
    function getLabel();
}

abstract class aUnit implements iUnit
{
    private $id;
    private $label;
    private $disables;
    private $note;
    private $options;
    protected $device1;
    private $device2;
    private $device3;
    private $device4;
    private $device5;
    private $device6;

    public function __construct($id, $label, $disables, $note, $options,
                                $device1 = null, $device2 = null, $device3 = null, $device4 = null, $device5 = null, $device6 = null)
    {
        $this->id = $id;
        $this->label = $label;
        $this->disables = $disables;
        $this->note = $note;
        $this->options = $options;
        $this->device1 = $device1;
        $this->device2 = $device2;
        $this->device3 = $device3;
        $this->device4 = $device4;
        $this->device5 = $device5;
        $this->device6 = $device6;
    }

    function getId()
    {
        return $this->id;
    }

    function getLabel()
    {
        return $this->label;
    }

    public function getDisables()
    {
        return $this->disables;
    }

    public function getNote()
    {
        return $this->note;
    }
}

require_once dirname(__FILE__) . '/units/temperature.unit.class.php';
