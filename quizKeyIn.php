<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 25.02.19
 * Time: 20:33
 */

require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/lists.class.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

$sel = new selectOption();
$sel->set('DeviceTypeID', typeDevice::KEY_IN);
$sel->set('Disabled', 0);

$keyInUnits = managerUnits::getListUnitsDB($sel);

unset($keyInUnits);