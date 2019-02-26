<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:49
 */

require_once(dirname(__FILE__).'/../class/globalConst.interface.php');
require_once(dirname(__FILE__).'/../class/lists.class.php');
require_once(dirname(__FILE__).'/../class/managerUnits.class.php');

$sel = new selectOption();
$sel->set('UnitLabel', 'move_1');
$sel->set('Disabled', 0);

$listMove = managerUnits::getListUnits($sel);
if (count($listMove) == 1) {
    $unitMove = $listMove[0];
}
else {
    return;
}

$isMove = $unitMove->getValue();

unset($listMove);


?>