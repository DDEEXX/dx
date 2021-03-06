<?php

require_once(dirname(__FILE__) . '/lib2/jpgraph/jpgraph.php');
require_once(dirname(__FILE__) . '/lib2/jpgraph/jpgraph_bar.php');
require_once(dirname(__FILE__) . '/lib2/jpgraph/jpgraph_line.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');
require_once(dirname(__FILE__) . '/class/logger.class.php');
require_once(dirname(__FILE__) . '/class/globalConst.interface.php');

const DEFAULT_GR_WIDTH = 180;
const DEFAULT_GR_HEIGHT = 80;
const DEFAULT_GR_TYPE = graphType::BAR;
const LABEL = 'pressure_cube';
const DELTA_SETYBASE = 6;

$grType = DEFAULT_GR_TYPE;

function noData($width = DEFAULT_GR_WIDTH, $height = DEFAULT_GR_HEIGHT) {
    $Title = "NO DATA";
    $im = imagecreatetruecolor($width, $height);
    $blue = imagecolorallocate($im, 82, 114, 191);
    $trcolor = ImageColorAllocate($im, 0, 0, 0);
    ImageColorTransparent($im, $trcolor);
    $font = 'lib2/jpgraph/fonts/DejaVuSans.ttf';
    $bbox = imagettfbbox(12, 25, $font, $Title);
    $x = $bbox[0] + (imagesx($im) / 2) - ($bbox[4] / 2) - 25;
    $y = $bbox[1] + (imagesy($im) / 2) - ($bbox[5] / 2) - 5;
    imagettftext($im, 12, 25, $x, $y, $blue, $font, $Title);
    header('Content-Type: image/png');
    imagepng($im);
    imagedestroy($im);
}

$unit = managerUnits::getUnitLabelDB(LABEL);

if (is_null($unit)) {
    logger::writeLog('Молуль с именем :: ' . LABEL . ' :: не найден (pressureHistory.php)',
        loggerTypeMessage::ERROR, loggerName::ERROR);
    noData();
    exit();
}

$currentPressure = 0;
$value = $unit->readValue();
if (!is_null($value)) {
    $currentPressure = (double)$value['Value'];
}

$maxPressure = $currentPressure;
$minPressure = $currentPressure;
$pressure = array();
//Получаем среднее значение давления по интервалам в 2 часа
for ($i=0;$i<=10;$i=$i+2) {
    $p = $unit->getAverageForInterval(2, date('Y-m-d H:i:s', strtotime('-'.$i.' hour')) );
    $pressure[] = $p;
    $maxPressure = max($p, $maxPressure);
    $minPressure = max($p, $minPressure);
}

//$pressure = array(734, 734,735,736,738,738);
$hour = array('-2','-4','-6','-8','-10','-12');

if (count($pressure)>0) {

    //$interval = ceil($count_r / 30);
    $interval = 1;

    $graph = new Graph(DEFAULT_GR_WIDTH, DEFAULT_GR_HEIGHT, 'auto');
    $graph->SetScale("textlin");
    $graph->SetBox(false);
//    $graph->SetTickDensity(TICKD_DENSE);
//    $graph->SetAxisStyle(AXSTYLE_BOXOUT);

    $graph->xaxis->SetTickLabels($hour);
    $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
    $graph->xaxis->SetColor('lightblue');
    $graph->xaxis->SetLabelMargin(2);
    $graph->xaxis->HideLine();

    $graph->xaxis->HideTicks(true, true);
    $graph->xaxis->HideLine();
    $graph->xaxis->HideZeroLabel();

    $graph->xaxis->SetLabelSide(SIDE_DOWN);
//    $graph->xaxis->HideTicks();

    $graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
    $graph->yaxis->SetColor('lightblue');
    $graph->yaxis->HideLabels();
    $graph->yaxis->HideTicks(true, true);
    $graph->yaxis->HideLine();
    $graph->yaxis->HideZeroLabel();
    //$graph->yaxis->Hide();
    //$graph->yaxis->SetTextTickInterval(1,2)
    //$graph->yaxis->SetTickLabels(array('-8>','','','','','','>','','','','','','8 >'));


    $graph->ygrid->Show(true);
    $graph->ygrid->SetFill(false);
    $graph->ygrid->SetColor('#4d6893');

    $b1 = new BarPlot($pressure);
    $b1->SetYBase($currentPressure - DELTA_SETYBASE);
    $b1->SetWidth(15);
    $graph->Add($b1);

    //$graph->img->SetMargin(45, 2, 2, 20);
    $graph->img->SetTransparent("white");

    $graph->Stroke();

}
else {
    noData($width, $height);
}

