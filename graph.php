<?php
// $_GET['']:
// - label - название датчика температуры
// - t = line|bar - тип графика - линейный или столбчатая
// - date_from - day|week|month|[дата] - дата начала начала, если day|week|month - за день, неделю или месяц
// - date_to - дата окончания

require_once(dirname(__FILE__) . '/lib2/jpgraph/jpgraph.php');
require_once(dirname(__FILE__) . '/lib2/jpgraph/jpgraph_bar.php');
require_once(dirname(__FILE__) . '/lib2/jpgraph/jpgraph_line.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');
require_once(dirname(__FILE__) . '/class/globalConst.interface.php');

//Значения по умолчанию
$grType = graphType::LINE;
$width = 410;
$height = 160;
$date_from = null;
$date_to = null;
$variant = graphVariant::VAR1;

function noData_($width, $height) {
    $Title = 'NO DATA';
    $image = imagecreatetruecolor($width, $height);
    $blueColor = imagecolorallocate($image, 0, 0, 255);
    $transparentColor = imagecolorallocate($image, 0, 0, 0); //черный цвет будет прозрачным
    imagecolortransparent($image, $transparentColor);
    $font = 'lib2/jpgraph/fonts/DejaVuSans.ttf';
    $bbox = imagettfbbox(14, 45, $font, $Title);
    $x = $bbox[0] + (imagesx($image) / 2) - ($bbox[4] / 2) - 25;
    $y = $bbox[1] + (imagesy($image) / 2) - ($bbox[5] / 2) - 5;
    imagettftext($image, 14, 45, $x, $y, $blueColor, $font, $Title);
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

if (isset($_GET['t'])) $grType = $_GET['t']; //Вид графика линейный или столбчатый
if (isset($_GET['var'])) $variant = $_GET['var']; //Вариант графика (см. graphVar)
if (isset($_GET['width'])) $width = $_GET['width'];
if (isset($_GET['height'])) $height = $_GET['height'];
if (!isset($_GET['date_from'])) $date_from = $_GET['date_from'];
if (!isset($_GET['date_to'])) $date_to = $_GET['date_to'];

$label = $_GET['label'];
if (!isset($label)) {
    logger::writeLog('Не задано имя модуля (graph.php)',
        loggerTypeMessage::ERROR, loggerName::ERROR);
    noData_($width, $height);
    exit();
}

$unit = managerUnits::getUnitLabel($label);
if (is_null($unit)) {
    logger::writeLog('Модуль с именем :: ' . $label . ' :: не найден (graph.php)',
        loggerTypeMessage::ERROR, loggerName::ERROR);
    noData_($width, $height);
    exit();
}

$result = $unit->getValuesForInterval($date_from, $date_to);

$count_r = count($result);
$x_data = [];
$y_data = [];

for ($i = 0; $i < $count_r; $i++) {
    $y_data[$i] = round($result[$i]['Value'], 1);
    $x_data[$i] = $result[$i]['Date_f'];
}

if ($count_r > 1) {

    $interval = ceil($count_r / 30);

    $graph = new Graph($width, $height, 'auto');
    $graph->SetScale("textlin");
    $graph->SetBox(false);
    $graph->SetTickDensity(TICKD_DENSE);
    $graph->SetAxisStyle(AXSTYLE_BOXOUT);

    $graph->xaxis->SetTickLabels($x_data);
    $graph->xaxis->SetTextLabelInterval(2);
    $graph->xaxis->HideTicks();
    $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
    $graph->xaxis->SetColor('lightblue');
    $graph->xaxis->SetLabelAngle(90);
    $graph->xaxis->HideLine();
    $graph->xaxis->SetTextTickInterval($interval);

    $graph->ygrid->Show(true);
    $graph->ygrid->SetFill(false);
    $graph->ygrid->SetColor('#4d6893');

    $graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
    $graph->yaxis->HideLine();
    $graph->yaxis->HideFirstLastLabel();
    $graph->yaxis->SetColor('lightblue');

    if ($grType == graphType::LINE) {
        $l1 = new LinePlot($y_data);
        $graph->Add($l1);
        $l1->SetColor('#99ffff');
        $l1->SetWeight(1);
    }
    elseif ($grType == graphType::BAR) {
        $b1 = new BarPlot($y_data);
        $graph->Add($b1);
        $b1->SetWidth(0.1);
    }

    $graph->img->SetMargin(45, 5, 5, 60);
    $graph->img->SetTransparent("white");

    $graph->Stroke();

}
else {
    noData_($width, $height);
}