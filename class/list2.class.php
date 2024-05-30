<?php

namespace units2;

interface iListFilter extends \Iterator
{
    function append($key, $value);
}

class listFilter implements iListFilter
{
    private $available;
    private $list = [];

    public function __construct($available)
    {
        $this->available = $available;
    }

    function rewind()
    {
        return reset($this->list);
    }

    function current()
    {
        return current($this->list);
    }

    function key()
    {
        return key($this->list);
    }

    function next()
    {
        return next($this->list);
    }

    function valid()
    {
        return key($this->list) !== null;
    }

    function append($key, $value)
    {
        if (in_array($key, $this->available, true))
            $this->list[$key] = $value;
    }
}

class unitsFilter extends listFilter
{
    public function __construct()
    {
        parent::__construct(['ID', 'Label', 'Type', 'Disabled']);
    }
}