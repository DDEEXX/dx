<?php
// $_GET['']:
// - label - название датчика температуры
// - t = line|bar - тип графика - линейный или столбчатая
// - date_from - day|week|month|[дата] - дата начала начала, если day|week|month - за день или месяц
// - date_to - дата окончания

require_once(dirname(__FILE__).'/lib2/jpgraph/jpgraph.php');
require_once(dirname(__FILE__).'/lib2/jpgraph/jpgraph_bar.php');
require_once(dirname(__FILE__).'/lib2/jpgraph/jpgraph.php');

//Вид графика линейный или столбчатый
$grType = $_GET['t'];
if ( !isset($grType) || empty($grType) ) {
    $grType = 'line';
}

//Если конечная дата не задана, используем настоящее время
if ( !isset($_GET['date_to']) || empty($_GET['date_to']) ) {
	$date_to = "NOW()";
}
else {
	$date_to = "'".$_GET['date_to']."'";
}

//Обрабатываем начальную дату
if ( !isset($_GET['date_from']) || empty($_GET['date_from']) || $_GET['date_from'] == 'day') {
	$date_from = "($date_to - INTERVAL 1 DAY)";
	$date_format = "DATE_FORMAT(Date, '%H:%i')";
}
elseif ( $_GET['date_from'] == "month" ) {
	$date_from = "($date_to - INTERVAL 1 MONTH)";
	$date_format = "DATE_FORMAT(Date, '%d.%m')";
}
else {
	$date_from = $_GET['date_from'];
	$date_format = "DATE_FORMAT(Date, '%d.%m')";
}

$id = $d->getId($_GET['label']);
if (!$id) {	// такой записи нет в таблице tunits, надо что-то записать в лог, сделаю позже
	$d->writeLog("В таблице tunits нет записи с UnitLabel = ".$_GET['label']);
	exit("#id");
}

$nameTabValue = $d->getTabValue($id);
if (!$d->chekTab($nameTabValue)) { // в БД нет таблицы и именем $nameTabValue, надо что-то записать в лог, сделаю позже
	$d->writeLog("B БД нет таблицы и именем ".$nameTabValue);
	exit("#tab");
}

$quely = "SELECT Value, $date_format Date_f FROM ".$nameTabValue." WHERE UnitID=".$id." AND Date>=$date_from AND Date<=$date_to ORDER BY Date";

$result = $d->get_array($quely);

$count_r = count($result);

for ( $i = 0; $i < $count_r; $i++ ) {
	$ydata[$i] = round($result[$i]['Value'],1);
	$xdata[$i] = $result[$i]['Date_f'];
}

if ($count_r > 1) {

	$interval = ceil($count_r / 30);

	$graph = new Graph(410,160,'auto');
	$graph->SetScale("textlin");
	$graph->SetBox(false);
	$graph->SetTickDensity(TICKD_DENSE);
	$graph->SetAxisStyle(AXSTYLE_BOXOUT);

	$graph->xaxis->SetTickLabels($xdata);
	$graph->xaxis->SetTextLabelInterval(2);
	$graph->xaxis->HideTicks();
	$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->SetColor('lightblue');
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->HideLine();
	$graph->xaxis->SetTextTickInterval($interval);

	$graph->ygrid->Show(true);
	$graph->ygrid->SetFill(false);
	$graph->ygrid->SetColor('#4d6893');
	
	$graph->yaxis->SetFont(FF_FONT1,FS_BOLD);
	$graph->yaxis->HideLine();
	$graph->yaxis->HideFirstLastLabel(); 
	$graph->yaxis->SetColor('lightblue');

	if ($_GET['t'] == 'line') {
		$l1=new LinePlot($ydata);
		$graph->Add($l1);
		$l1->SetColor('#99ffff');
		$l1->SetWeight(1);
	}
	elseif ($_GET['t'] = 'bar') {
		$b1 = new BarPlot($ydata);
		$graph->Add($b1);
		$b1->SetWidth(0.1);
	}

	$graph->img->SetMargin(45,5,5,60);
	$graph->img->SetTransparent("white");
	
	$graph->Stroke();
	
}
else {
	
$Title = "За этот период нет данных!";
$Title = "NO DATA";
	
$im = imagecreatetruecolor(370, 160);
$blue = imagecolorallocate($im, 0, 0, 255);
$trcolor = ImageColorAllocate($im, 0, 0, 0);
ImageColorTransparent($im , $trcolor); 
$font = 'lib2/jpgraph/fonts/DejaVuSans.ttf';
$bbox = imagettfbbox(14, 45, $font, $Title);
$x = $bbox[0] + (imagesx($im) / 2) - ($bbox[4] / 2) - 25;
$y = $bbox[1] + (imagesy($im) / 2) - ($bbox[5] / 2) - 5;
imagettftext($im, 14, 45, $x, $y, $blue, $font, $Title);
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);

}
?>
