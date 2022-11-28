<?php

require_once(dirname(__FILE__) . '/class/graphUnitValues.class.php');

$urlImage = '';
if (isset($_GET['image'])) $urlImage =$_GET['image'];

$label = $_GET['label'];
$type = $_GET['graph']['type'];
$variant = $_GET['graph']['variant'];
$width = $_GET['graph']['width'];
$height = $_GET['graph']['height'];
$count = $_GET['graph']['count'];
$minDelta = $_GET['graph']['min_delta'];

$graphWidget = new graphUnitValues($label, $type, $variant, $width, $height,null, null, $count, $minDelta);
$imageGraph64 = $graphWidget->getGraph64();

echo
'<div style="margin-left: 5px; margin-top: 5px">'.
    '<div style="float: left">'.
        '<img src="'.$urlImage.'">'.
    '</div>'.
    '<div style="margin-left: 5px; float: left">'.
        '<img src="data:image/png;base64,'.$imageGraph64.'"/>'.
    '</div>'.
'</div>';