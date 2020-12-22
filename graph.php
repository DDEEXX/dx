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

const DEFAULT_GR_WIDTH = 410;
const DEFAULT_GR_HEIGHT = 160;
const DEFAULT_GR_TYPE = graphType::LINE;

function noData($width = DEFAULT_GR_WIDTH, $height = DEFAULT_GR_HEIGHT) {
    $Title = "NO DATA";
    $im = imagecreatetruecolor($width, $height);
    $blue = imagecolorallocate($im, 0, 0, 255);
    $trcolor = ImageColorAllocate($im, 0, 0, 0);
    ImageColorTransparent($im, $trcolor);
    $font = 'lib2/jpgraph/fonts/DejaVuSans.ttf';
    $bbox = imagettfbbox(14, 45, $font, $Title);
    $x = $bbox[0] + (imagesx($im) / 2) - ($bbox[4] / 2) - 25;
    $y = $bbox[1] + (imagesy($im) / 2) - ($bbox[5] / 2) - 5;
    imagettftext($im, 14, 45, $x, $y, $blue, $font, $Title);
    header('Content-Type: image/png');
    imagepng($im);
    imagedestroy($im);
}

//Вид графика линейный или столбчатый
if (!isset($_GET['t'])) {
    $_GET['t'] = DEFAULT_GR_TYPE;
}

if (!isset($_GET['date_from'])) {
    $_GET['date_from'] = null;
}
if (!isset($_GET['date_to'])) {
    $_GET['date_to'] = null;
}

if (!isset($_GET['width'])) {
    $_GET['width'] = DEFAULT_GR_WIDTH;
}

if (!isset($_GET['height'])) {
    $_GET['height'] = DEFAULT_GR_HEIGHT;
}

$grType = $_GET['t'];
$width = $_GET['width'];
$height = $_GET['height'];

$label = $_GET['label'];

$unit = managerUnits::getUnitLabelDB($label);

if (is_null($unit)) {
    logger::writeLog('Молуль с именем :: ' . $label . ' :: не найден (graph.php)',
        loggerTypeMessage::ERROR, loggerName::ERROR);
    noData($width, $height);
    //exit("#label");
    exit();
}

$result = $unit->getTemperatureForInterval($_GET['date_from'], $_GET['date_to']);

$count_r = count($result);
$xdata = array();
$ydata = array();

for ($i = 0; $i < $count_r; $i++) {
    $ydata[$i] = round($result[$i]['Value'], 1);
    $xdata[$i] = $result[$i]['Date_f'];
}

if ($count_r > 1) {

    $interval = ceil($count_r / 30);

    $graph = new Graph($width, $height, 'auto');
    $graph->SetScale("textlin");
    $graph->SetBox(false);
    $graph->SetTickDensity(TICKD_DENSE);
    $graph->SetAxisStyle(AXSTYLE_BOXOUT);

    /** @noinspection PhpUndefinedVariableInspection */
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->xaxis->SetTickLabels($xdata);
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->xaxis->SetTextLabelInterval(2);
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->xaxis->HideTicks();
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->xaxis->SetColor('lightblue');
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->xaxis->SetLabelAngle(90);
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->xaxis->HideLine();
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->xaxis->SetTextTickInterval($interval);

    /** @noinspection PhpUndefinedMethodInspection */
    $graph->ygrid->Show(true);
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->ygrid->SetFill(false);
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->ygrid->SetColor('#4d6893');

    /** @noinspection PhpUndefinedMethodInspection */
    $graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->yaxis->HideLine();
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->yaxis->HideFirstLastLabel();
    /** @noinspection PhpUndefinedMethodInspection */
    $graph->yaxis->SetColor('lightblue');

    if ($grType == graphType::LINE) {
        /** @noinspection PhpUndefinedVariableInspection */
        $l1 = new LinePlot($ydata);
        $graph->Add($l1);
        $l1->SetColor('#99ffff');
        $l1->SetWeight(1);
    }
    elseif ($grType == graphType::BAR) {
        /** @noinspection PhpUndefinedVariableInspection */
        $b1 = new BarPlot($ydata);
        $graph->Add($b1);
        $b1->SetWidth(0.1);
    }

    $graph->img->SetMargin(45, 5, 5, 60);
    $graph->img->SetTransparent("white");

    $graph->Stroke();

}
else {
    noData($width, $height);
}