<?php

class temperatureCurve {

    public $curve = 1;    //наклон кривой
    public $dK = 1;       //коэффициент смещения
    public $dT = 1;       //коэффициент адаптации

    public function getTemperature($tIn, $tOut, $tTarget) {
        $tC = -1 * $this->curve * $tOut + ((20 + 20 /$this->curve) * $this->curve);
        $tK = ($tTarget - 20) * $this->dK;
        $tT = ($tTarget - $tIn) * $this->dT;
        return $tC + $tK + $tT;
    }
}

class pidTemperature
{
    private $tCurve;
    public $target;

    public function __construct($target)
    {
        $this->tCurve = new temperatureCurve;
        $this->target = $target;
    }

    public function getTempCurve($tIn, $tOut)
    {
        return $this->tCurve->getTemperature($tIn, $tOut, $this->target);
    }

    public function setCurve($cur, $dk, $dt)
    {
        $this->tCurve->curve = $cur;
        $this->tCurve->dK = $dk;
        $this->tCurve->dT = $dt;
    }
}

class pidTemperatureBoiler
{
    private $tOut;  //температура наружная
    private $tIn; //температура текущая
    private $tBoiler;
    private $tFloor;

    public function __construct()
    {
        $this->tBoiler = new pidTemperature(20);
        $this->tFloor = new pidTemperature(20);
    }

    public function getBoilerCurrentTempCurve() {
        return $this->getBoilerTempCurve($this->tIn, $this->tOut);
    }

    public function getBoilerTempCurve($tIn, $tOut) {
        return $this->tBoiler->getTempCurve($tIn, $tOut);
    }




}