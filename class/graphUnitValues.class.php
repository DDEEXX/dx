<?php

require_once(dirname(__FILE__) . '/../lib2/jpgraph/jpgraph.php');
require_once(dirname(__FILE__) . '/../lib2/jpgraph/jpgraph_bar.php');
require_once(dirname(__FILE__) . '/../lib2/jpgraph/jpgraph_line.php');
require_once(dirname(__FILE__) . '/managerUnits.class.php');
require_once(dirname(__FILE__) . '/logger.class.php');
require_once(dirname(__FILE__) . '/globalConst.interface.php');

interface iGraphUnitValues {

    const WIDTH = 160;
    const HEIGHT = 410;

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

    public function __construct($label, $type = graphType::LINE, $variant = graphVariant::VAR1,
                                $width = iGraphUnitValues::WIDTH, $height = iGraphUnitValues::HEIGHT,
                                $date_from = null, $date_to = null)
    {
        if (!empty($label))
            $this->unit = managerUnits::getUnitLabel($label);
        $this->type = $type;
        $this->variant = $variant;
        $this->height = $height;
        $this->width = $width;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }

    public function getGraph64()
    {
        if (!is_null($this->unit)) {
            return $this->drawGraph();
        }
        else {
            return $this->noData();
        }
    }

    private function noData()
    {
        $Title = 'NO DATA';
        $image = imagecreatetruecolor($this->width, $this->height);
        $blueColor = imagecolorallocate($image, 0, 0, 255);
        $transparentColor = imagecolorallocate($image, 0, 0, 0); //черный цвет будет прозрачным
        imagecolortransparent($image, $transparentColor);
        $font = './lib2/jpgraph/fonts/DejaVuSans.ttf';
        $bbox = imagettfbbox(14, 45, $font, $Title);
        $x = $bbox[0] + (imagesx($image) / 2) - ($bbox[4] / 2) - 25;
        $y = $bbox[1] + (imagesy($image) / 2) - ($bbox[5] / 2) - 5;
        imagettftext($image, 14, 45, $x, $y, $blueColor, $font, $Title);
        ob_start();
//        header('Content-Type: image/png');
        imagepng($image);
        $buffer = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        return base64_encode($buffer);
    }

    private function drawGraph (){

        if (is_null($this->unit)) {
            return noData();
        }

        $values = $this->unit->getValuesForInterval($this->date_from, $this->date_to);

        $count_r = count($values);
        $x_data = [];
        $y_data = [];

        for ($i = 0; $i < $count_r; $i++) {
            $y_data[$i] = round($values[$i]['Value'], 1);
            $x_data[$i] = $values[$i]['Date_f'];
        }

        if ($count_r > 1) {

            $interval = ceil($count_r / 30);

            $graph = new Graph($this->width, $this->height, 'auto');
            $graph->SetScale('textlin');
            $graph->SetBox(false);
            $graph->SetTickDensity(TICKD_DENSE);
            $graph->SetAxisStyle(AXSTYLE_BOXOUT);

            if ($this->variant == graphVariant::VAR1) {
                $graph->xaxis->Hide();

                $graph->img->SetMargin(45, 0, 0, 0);
            } elseif ($this->variant == graphVariant::VAR2) {
                $graph->xaxis->SetTickLabels($x_data);
                $graph->xaxis->SetTextLabelInterval(2);
                $graph->xaxis->HideTicks();
                $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
                $graph->xaxis->SetColor('lightblue');
                $graph->xaxis->SetLabelAngle(90);
                $graph->xaxis->HideLine();
                $graph->xaxis->SetTextTickInterval($interval);

                $graph->img->SetMargin(45, 5, 5, 60);
            }

            $graph->ygrid->Show(true);
            $graph->ygrid->SetFill(false);
            $graph->ygrid->SetColor('#4d6893');

            $graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
            $graph->yaxis->HideLine();
            $graph->yaxis->HideFirstLastLabel();
            $graph->yaxis->SetColor('lightblue');

            if ($this->type == graphType::LINE) {
                $l1 = new LinePlot($y_data);
                $graph->Add($l1);
                $l1->SetColor('#99ffff');
                $l1->SetWeight(1);
            }
            elseif ($this->type == graphType::BAR) {
                $b1 = new BarPlot($y_data);
                $graph->Add($b1);
                $b1->SetWidth(0.1);
            }

            $graph->img->SetTransparent('white');

            ob_start();
            $graph->Stroke();
            $buffer = ob_get_contents();
            ob_end_clean();
            return base64_encode($buffer);

        }
        else {
            return $this->noData();
        }

    }

}