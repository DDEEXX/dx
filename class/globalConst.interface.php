<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 26.11.18
 * Time: 20:15
 */

interface netDevice {
    const NONE          = 0;
    const ONE_WIRE      = 1;
    const ETHERNET      = 2;
    const CUBIEBOARD    = 3;
}

interface typeDevice{
    const NONE              = 0;
    const TEMPERATURE       = 1;
    const LABEL             = 2;
    const POWER_KEY         = 3;
    const KEY_IN            = 4;
    const KEY_OUT           = 5;
}
