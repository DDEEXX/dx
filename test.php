<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">

<head>
    <title>без имени</title>
</head>

<body>

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


$unitMove = managerUnits::getUnitLabel('move_1');

if (is_null($unitMove)) return;

$isMove = $unitMove->getValue();

unset($unitMove);
?>

</body>

</html>
