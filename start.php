<?php

require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

$flagStart = managerUnits::initUnits();
if (!$flagStart) {
    die(21);
}


