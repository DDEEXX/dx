<?php

require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/../class/managerUnits.class.php');

/**
 * Запись в модуль с именем $label, данных value
 */

if (isset($_REQUEST['label'])) $labels = $_REQUEST['label'];
else return;

if ($_REQUEST['value']) $value['value'] = $_REQUEST['value'];
else return;

$labels = explode(LABELS_SEPARATOR, $labels);
foreach ($labels as $label) {
    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) return;

    if ($unit instanceof iModuleUnite)
        $unit->setData(json_encode($value));
}