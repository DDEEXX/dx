<?php
$unite = managerUnits::getUnitLabel('kitchen_hood');
$data = $unite->getData();

$value = json_decode($data, true);

if (is_array($value)) {
    $mode = $value['mode'];


    echo $data;

} else {


}