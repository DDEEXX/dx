<?php

require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');

/**
 * Запись в модуль с именем $label, данных value
 */

if (isset($_REQUEST['label'])) $label = $_REQUEST['label'];
else return;

$unit = managerUnits::getUnitLabel($label);
if (is_null($unit)) return;

if ($_REQUEST['value']) $value['value'] = $_REQUEST['value'];
else return;

if ($unit instanceof iModuleUnite)
    $unit->setData(json_encode($value));
