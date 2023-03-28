<?php
$label = $_GET['label'];
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
    $valueGas = $value['value'];
}

echo '<div id="kitchen_gas_sensor_mode" style="margin-top:4px">';
echo '    <div style="display: flex">';
echo '      <div style="margin: 2px; width: 15px; height: 7px; background-color: '.$colorSensor.'"></div>';
echo '      <div style="margin-left: 5px">'.$valueGas.'</div>';
echo '      <input id="kitchen_gas_sensor_last_status" value=' . $dateLastStatus . ' type="hidden"/>';
echo '    </div>';
echo '</div>';

