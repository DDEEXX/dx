<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:49
 */

require_once(dirname(__FILE__).'/class/globalConst.interface.php');
require_once(dirname(__FILE__).'/class/lists.class.php');
require_once(dirname(__FILE__).'/class/managerUnits.class.php');
require_once(dirname(__FILE__).'/class/sunInfo.class.php');

$NAME_LIGHT_N = 'light_hol_2_n';

$unitNightLight = managerUnits::getUnitLabel($NAME_LIGHT_N);

$unitNightLight->setValue(0);

unset($unitNightLight);

?>