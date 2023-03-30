<?php
$label = $_GET['label'];
$title = $_GET['title'];
$unit = managerUnits::getUnitLabel($label);
$value = null;
$dateLastStatus = '';
if (!is_null($unit)) {
    $data = $unit->getData();
    if (!is_null($data)) {
        $value = json_decode($data['value'], true);
        $dateLastStatus = $data['date'];
    }
}

$colorSensor = '#323200';
$valueGas = '';
if (is_array($value)) {
    $colorSensor = '#00ff00';
    if ($value['alarm']) {
        $colorSensor = '#ff0000';
    }
    $valueGas = $value['gas'];
}

echo '<div style="display: flex">';
echo '    <div style="width: 70px"><p>'.$title.'</p></div>';
echo '    <div style="margin: 2px; width: 15px; height: 7px; background-color: '.$colorSensor.'"></div>';
echo '    <div style="margin-left: 5px">'.$valueGas.'</div>';
echo '    <input id="'.$label.'_last_update" value=' . $dateLastStatus . ' type="hidden"/>';
echo '</div>';

