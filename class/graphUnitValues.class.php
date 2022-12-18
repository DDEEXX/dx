<?php

require_once(dirname(__FILE__) . '/../lib2/jpgraph/jpgraph.php');
require_once(dirname(__FILE__) . '/../lib2/jpgraph/jpgraph_bar.php');
require_once(dirname(__FILE__) . '/../lib2/jpgraph/jpgraph_line.php');
require_once(dirname(__FILE__) . '/managerUnits.class.php');
require_once(dirname(__FILE__) . '/logger.class.php');
require_once(dirname(__FILE__) . '/globalConst.interface.php');

interface iGraphUnitValues
{

    const WIDTH = 150;
    const HEIGHT = 100;

    public function getGraph64();

}

class graphUnitValues implements iGraphUnitValues
{
    private $unit = null;
    private $width;
    private $height;
    private $type;
    private $variant;
    private $date_from;
    private $date_to;
    private $count;
    private $minDelta;

    public function __construct($unit, $type = graphType::LINE, $variant = graphVariant::VAR1,
                                $width = iGraphUnitValues::WIDTH, $height = iGraphUnitValues::HEIGHT,
                                $date_from = null, $date_to = null,
                                $count = 6, $minDelta = 10)
    {
        $this->unit = $unit;
        $this->type = $type;
        $this->variant = $variant;
        $this->height = $height;
        $this->width = $width;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->count = $count;
        $this->minDelta = $minDelta;
    }

    public function getGraph64()
    {
        if (!is_null($this->unit)) {
            return $this->drawGraph();
        } else {
            return static::noData($this->width, $this->height);
        }
    }

    public static function noData($width = null, $height = null)
    {
        if (empty($width)) { $width = iGraphUnitValues::WIDTH;}
        if (empty($height)) { $height = iGraphUnitValues::HEIGHT;}
        $Title = 'NO DATA';
        $image = imagecreatetruecolor($width, $height);
        $blueColor = imagecolorallocate($image, 0, 0, 255);
        $transparentColor = imagecolorallocate($image, 0, 0, 0); //черный цвет будет прозрачным
        imagecolortransparent($image, $transparentColor);
        $font = './lib2/jpgraph/fonts/DejaVuSans.ttf';
        $bbox = imagettfbbox(14, 45, $font, $Title);
        $x = $bbox[0] + (imagesx($image) / 2) - ($bbox[4] / 2) - 25;
        $y = $bbox[1] + (imagesy($image) / 2) - ($bbox[5] / 2) - 5;
        imagettftext($image, 14, 45, $x, $y, $blueColor, $font, $Title);
        ob_start();
        imagepng($image);
        $buffer = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        return base64_encode($buffer);
    }

    private function drawGraphVariant3()
    {

        $y_data = [];
        $x_data = [];
        $delta = $this->minDelta;

        //Получаем среднее значение давления по интервалам в 2 часа
        $everyHour = (int)(24 / (int)$this->count);
        $curTime = time();
        for ($i = 0; $i < $this->count; $i++) {
            $avgValue = $this->unit->getValueAverageForInterval(date('Y-m-d H:i:s', strtotime('-' . $i . ' hour', $curTime)), -$everyHour);
            $y_data[] = is_null($avgValue) ? null : round($avgValue);
            $x_data[] = -(($i + 1) * $everyHour);
        }
        //находим какое-то значение не равное 0, от которого можно отталкиваться при поиске максима и минимума
        $curValue = null;
        for ($i = 0; $i < $this->count; $i++) {
            if (!is_null($y_data[$i])) {
                $curValue = $y_data[$i];
                break;
            }
        }
        if (!is_null($curValue)) {
            $maxValue = $curValue;
            $minValue = $curValue;
            for ($i = 0; $i < $this->count; $i++) {
                if (!is_null($y_data[$i])) {
                    $maxValue = max($maxValue, $y_data[$i]);
                    $minValue = min($minValue, $y_data[$i]);
                }
            }
            $deltaUp = $maxValue - $curValue; //дельта между текущим показанием и максимумом
            $deltaDown = ($curValue - $minValue) + 1; //дельта между текущим показанием и минимумом (+1 чтобы снизу был хоть 1)
            //текущее показание всегда посередине
            $delta = max($deltaUp, $deltaDown, (int)$this->minDelta);
            //Приводим показания к единицам, где текущее показания будет 0, а бывшие null - минимальные из возможных
            for ($i = 0; $i < $this->count; $i++) {
                if (!is_null($y_data[$i])) {
                    $y_data[$i] = $y_data[$i] - $curValue + $delta;
                } else {
                    $y_data[$i] = 0;
                }
            }
        } else {
            for ($i = 0; $i < $this->count; $i++)
                $y_data[$i] = 0;

        }

        $graph = new Graph($this->width, $this->height, 'auto');
        $graph->SetScale('textlin', 0, $delta * 2);
        $graph->img->SetMargin(45, 5, 5, 20);
        $graph->SetBox(false);

        $graph->xaxis->SetTickLabels($x_data);
        $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
        $graph->xaxis->SetColor('lightblue');
        $graph->xaxis->SetLabelMargin(2);
        $graph->xaxis->HideLine();
        $graph->xaxis->HideTicks(true, true);
        $graph->xaxis->HideLine();
        $graph->xaxis->HideZeroLabel();
        $graph->xaxis->SetLabelSide(SIDE_DOWN);

        $graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
        $graph->yaxis->SetColor('lightblue');
        //$graph->yaxis->HideLabels();
        $graph->yaxis->HideTicks(true, true);
        $graph->yaxis->HideLine();
        //$graph->yaxis->HideZeroLabel();

        $tickLabels = [];
        for ($i = 1; $i < $delta * 2; $i++)
            $tickLabels[$i] = $i - $delta;
        $tickLabels[0] = '' . (-$delta) . ' >';
        $tickLabels[$delta] = '>';
        $tickLabels[$delta * 2] = '' . ($delta) . ' >';

        $graph->yaxis->SetTickLabels($tickLabels);

        $graph->xaxis->SetPos('min');

        $graph->ygrid->Show(true);
        $graph->ygrid->SetFill(false);
        $graph->ygrid->SetColor('#4d6893');

        //$graph->yaxis->scale->ticks->Set(10);

        $b1 = new BarPlot($y_data);
        $b1->SetYBase($delta * 2);
        $b1->SetWidth(15);
        $graph->Add($b1);

        //$graph->img->SetMargin(45, 2, 2, 20);
        $graph->img->SetTransparent('white');

        ob_start();
        $graph->Stroke();
        $buffer = ob_get_contents();
        ob_end_clean();
        return base64_encode($buffer);

    }

    private function drawGraph()
    {

        if (is_null($this->unit)) {
            return static::noData($this->width, $this->height);
        }

        $y_data = [];
        $x_data = [];

        if ($this->type == 3) {
            return $this->drawGraphVariant3();
        } else {
            $values = $this->unit->getValuesForInterval($this->date_from, $this->date_to,'%H');
            $count_r = count($values);
            for ($i = 0; $i < $count_r; $i++) {
                $y_data[$i] = round($values[$i]['Value'], 1);
                $x_data[$i] = $values[$i]['Date_f'];
            }
        }

        if (count($x_data) > 1) {

            $interval = ceil(count($x_data) / $this->count);

            $graph = new Graph($this->width, $this->height, 'auto');
            $graph->SetScale('textlin');
            $graph->SetBox(false);
            $graph->SetTickDensity(TICKD_DENSE);
            //$graph->SetAxisStyle(AXSTYLE_BOXOUT);

            if ($this->variant == graphVariant::VAR1) {
                $graph->xaxis->HideTicks();
                $graph->xaxis->HideLine();
                $graph->xaxis->SetTickLabels($x_data);
                $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
                $graph->xaxis->SetColor('lightblue');
                $graph->xaxis->SetLabelMargin(2);
                $graph->xaxis->SetTextTickInterval($interval);
                $graph->xaxis->SetPos('min');

                $graph->yaxis->SetPos('max');
                $graph->yaxis->SetLabelSide('SIDE_RIGHT');
                $graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
                $graph->yaxis->HideLine();
                $graph->yaxis->HideFirstLastLabel();
                $graph->yaxis->SetColor('lightblue');

                $graph->img->SetMargin(0, 45, 0, 20);
            } elseif ($this->variant == graphVariant::VAR2) {
                $graph->xaxis->SetTickLabels($x_data);
                $graph->xaxis->SetTextLabelInterval(2);
                $graph->xaxis->HideTicks();
                $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
                $graph->xaxis->SetColor('lightblue');
                $graph->xaxis->SetLabelAngle(90);
                $graph->xaxis->HideLine();
                $graph->xaxis->SetTextTickInterval($interval);

                $graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
                $graph->yaxis->HideLine();
                $graph->yaxis->HideFirstLastLabel();
                $graph->yaxis->SetColor('lightblue');

                $graph->img->SetMargin(45, 5, 5, 60);
            }

            $graph->ygrid->Show(true);
            $graph->ygrid->SetFill(false);
            $graph->ygrid->SetColor('#4d6893');

            if ($this->type == graphType::LINE) {
                $l1 = new LinePlot($y_data);
                $graph->Add($l1);
                $l1->SetColor('#99ffff');
                $l1->SetWeight(1);
            } elseif ($this->type == graphType::BAR) {
                $b1 = new BarPlot($y_data);
                $graph->Add($b1);
                $b1->SetWidth(0.1);
            }

            $graph->img->SetAntiAliasing();
            $graph->img->SetTransparent('white');

            ob_start();
            $graph->Stroke();
            $buffer = ob_get_contents();
            ob_end_clean();
            return base64_encode($buffer);

        } else {
            return static::noData($this->width, $this->height);
        }

    }

}