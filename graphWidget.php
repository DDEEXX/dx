<?php

require_once(dirname(__FILE__) . '/class/graphUnitValues.class.php');

$urlImage = '';
if (isset($_GET['image'])) $urlImage =$_GET['image'];

$label = $_GET['label'];
$type = $_GET['graph']['type'];
$variant = $_GET['graph']['variant'];
$width = $_GET['graph']['width'];
$height = $_GET['graph']['height'];

$graphWidget = new graphUnitValues($label, $type, $variant, $width, $height);
$imageGraph64 = $graphWidget->getGraph64();

echo
'<div id="block_weather_outdoor_data_widger" style="margin-left: 5px; margin-top: 5px">'.
    '<div>'.
        '<img src="'.$urlImage.'" style="float: left">'.
    '</div>'.
    '<div>'.
        '<img src="data:image/png;base64,'.$imageGraph64.'"/>'.
    '</div>'.
'</div>';