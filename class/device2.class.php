<?php

namespace units2;

interface iDevice
{
    function getId();
}

abstract class aDevice implements iDevice
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

interface iModelDevice
{
    function ping();
}

abstract class aModelDevice implements iModelDevice
{
    private $address;

}