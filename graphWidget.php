<?php

require_once(dirname(__FILE__) . '/class/graphUnitValues.class.php');

$options = [
    'label'=>null,
    'iconURL'=>'img2/icon_graph_default.png',
    'iconWidth'=>null,
    'iconHeight'=>null,
    'curValueShow'=>false,
    'curValueTop'=>0,
    'curValueLeft'=>0,
    'curValuePrecision'=>0,
    'curValuePostfix'=>'',
    'type'=>0,
    'variant'=>0,
    'top'=> '0px',
    'width'=>100,
    'height'=>100,
    'count'=>6,
    'minDelta'=>4,
    'caption'=>[
        'show'=>false,
        'text'=>'Сервер',
        'top'=> '0px',
        'left'=> '0px'
    ]
];

$curValueNumeric = '--';

if (isset($_POST['label'])) { $options['label'] = $_POST['label'];}
if (!is_null($options['label'])) {

    if (isset($_POST['icon']['image'])) { $options['iconURL'] = $_POST['icon']['image']; }
    if (isset($_POST['icon']['width'])) { $options['iconWidth'] = $_POST['icon']['width']; }
    if (isset($_POST['icon']['height'])) { $options['iconHeight'] = $_POST['icon']['height']; }

    if (isset($_POST['graph']['type'])) { $options['type'] = $_POST['graph']['type']; }
    if (isset($_POST['graph']['variant'])) { $options['variant'] = $_POST['graph']['variant']; }
    if (isset($_POST['graph']['width'])) { $options['width'] = $_POST['graph']['width']; }
    if (isset($_POST['graph']['height'])) { $options['height'] = $_POST['graph']['height']; }
    if (isset($_POST['graph']['count'])) { $options['count'] = $_POST['graph']['count']; }
    if (isset($_POST['graph']['min_delta'])) { $options['minDelta'] = $_POST['graph']['min_delta']; }
    if (isset($_POST['graph']['top'])) { $options['top'] = $_POST['graph']['top']; }
    if (isset($_POST['graph']['left'])) { $options['left'] = $_POST['graph']['left']; }

    if (isset($_POST['currentValue']['show'])) { $options['curValueShow'] = (bool)$_POST['currentValue']['show']; }
    if (isset($_POST['currentValue']['top'])) { $options['curValueTop'] = $_POST['currentValue']['top']; }
    if (isset($_POST['currentValue']['precision'])) { $options['curValuePrecision'] = $_POST['currentValue']['precision']; }
    if (isset($_POST['currentValue']['postfix'])) { $options['curValuePostfix'] = $_POST['currentValue']['postfix']; }

    if (isset($_POST['caption']['show'])) { $options['caption']['show'] = $_POST['caption']['show']; }
    if (isset($_POST['caption']['text'])) { $options['caption']['text'] = $_POST['caption']['text']; }
    if (isset($_POST['caption']['top'])) { $options['caption']['top'] = $_POST['caption']['top']; }
    if (isset($_POST['caption']['left'])) { $options['caption']['left'] = $_POST['caption']['left']; }

    $unit = managerUnits::getUnitLabel($options['label']);
    if (is_null($unit)) {
        $imageGraph64 = graphUnitValues::noData();
    } else {
        $graphWidget = new graphUnitValues($unit,
            $options['type'],
            $options['variant'],
            $options['width'],
            $options['height'],
            null,
            null,
            $options['count'],
            $options['minDelta']);
        $imageGraph64 = $graphWidget->getGraph64();
        if ($options['curValueShow']) {
            $data = json_decode($unit->getData(), true);
            if (!$data['valueNull']) {
                $precision = (int)$options['curValuePrecision'];
                $curValueNumeric = round( (double)$data['value'], $precision);
            }
        }
    }
} else {
    $imageGraph64 = graphUnitValues::noData();
}

$iconWidth_ = empty($options['iconWidth'])?'':(' width="'.$options['iconWidth'].'"');
$iconHeight_ = empty($options['iconHeight'])?'':(' height="'.$options['iconHeight'].'"');

echo
'<div style="position: relative; margin-left: 5px; margin-top: 5px">'.
    '<div style="float: left">' .
        '<img src="'.$options['iconURL'].'" alt="i" ' .$iconHeight_ . $iconWidth_ . '>'.
    '</div>'.
    '<div style="margin-left: 5px; float: left; margin-top: '.$options['top'].'">'.
        '<img src="data:image/png;base64,'.$imageGraph64.'" alt="no data">'.
    '</div>';

if ($options['curValueShow']) {
echo '<div style="position: absolute; top: '.$options['curValueTop'].'; left: '.$options['curValueLeft'].'">'.
    $curValueNumeric.$options['curValuePostfix'].'</div>';
}
echo '</div>';

if ($options['caption']['show']) {
    echo '<div style="position: absolute; top: '.$options['caption']['top'].'; left: '.$options['caption']['left'].'">'.
    $options['caption']['text'].'</div>';
}
echo '</div>';