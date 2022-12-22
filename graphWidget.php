<?php

require_once(dirname(__FILE__) . '/class/graphUnitValues.class.php');

$label = $_POST['label'];

$iconURL = 'img2/icon_graph_default.png';
$iconWidth = null; $iconHeight = null;
$curValueShow = false;
$curValueTop = 0; $curValueLeft = 30;
$curValueNumeric = '--';
$curValuePrecision = 0;
$curValuePostfix = '';

if (isset($_POST['icon']['image'])) { $iconURL = $_POST['icon']['image']; }
if (isset($_POST['icon']['width'])) { $iconWidth = $_POST['icon']['width']; }
if (isset($_POST['icon']['height'])) { $iconHeight = $_POST['icon']['height']; }

if (isset($label)) {
    $type = $_POST['graph']['type'];
    $variant = $_POST['graph']['variant'];
    $width = $_POST['graph']['width'];
    $height = $_POST['graph']['height'];
    $count = $_POST['graph']['count'];
    $minDelta = $_POST['graph']['min_delta'];

    if (isset($_POST['currentValue']['show'])) {$curValueShow = $_POST['currentValue']['show'];}
    if (isset($_POST['currentValue']['top'])) {$curValueTop = $_POST['currentValue']['top'];}
    if (isset($_POST['currentValue']['left'])) {$curValueLeft = $_POST['currentValue']['left'];}
    if (isset($_POST['currentValue']['precision'])) {$curValuePrecision = $_POST['currentValue']['precision'];}
    if (isset($_POST['currentValue']['postfix'])) {$curValuePostfix = $_POST['currentValue']['postfix'];}

    $unit = managerUnits::getUnitLabel($label);
    if (is_null($unit)) {
        $imageGraph64 = graphUnitValues::noData();
    } else {
        $graphWidget = new graphUnitValues($unit, $type, $variant, $width, $height,null, null, $count, $minDelta);
        $imageGraph64 = $graphWidget->getGraph64();
        if ($curValueShow == 'true') {
            $data = json_decode($unit->getData(), true);
            if (!$data['valueNull']) {
                $precision = (int)$curValuePrecision;
                $curValueNumeric = round( (double)$data['value'], $precision);
            }
        }
    }
} else {
    $imageGraph64 = graphUnitValues::noData();
}

$iconWidth_ = empty($iconWidth)?'':(' width="'.$iconWidth.'"');
$iconHeight_ = empty($iconHeight)?'':(' height="'.$iconHeight.'"');

echo
'<div style="position: relative; margin-left: 5px; margin-top: 5px">'.
    '<div style="float: left">' .
        '<img src="'.$iconURL.'" alt="i" ' .$iconHeight_ . $iconWidth_ . '>'.
    '</div>'.
    '<div style="margin-left: 5px; float: left">'.
        '<img src="data:image/png;base64,'.$imageGraph64.'" alt="no data">'.
    '</div>';
if ($curValueShow == 'true') {
echo '<div style="position: absolute; margin-top: '.$curValueTop.'; margin-left: '.$curValueLeft.'">'.
    $curValueNumeric.$curValuePostfix.'</div>';
}
echo '</div>';