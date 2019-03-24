<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.03.19
 * Time: 19:12
 */

require_once(dirname(__FILE__)."/sqlDataBase.class.php");

interface dayPart {
    const MORNING   = 0;
    const DAY       = 1;
    const EVENING   = 2;
    const NIGHT     = 3;
}

interface twilight {
    const TWILIGHT_M = 30; //Утренние сумерки в секундах
    const TWILIGHT_E = 30; //Вечерние сумерки в секундах
}

class sunInfo {

    function sunSet() { //восход солнца

    }

    function sunRise() { //заход солнца

    }

    /**
     * N - если ночь, D - если день, M - если утро, E - если вечер
     * @param $time
     * @return int
     */
    public static function getSunInfo($time) {

        $latitude =  DB::getConst('latitude');
        $longitude = DB::getConst('longitude');
        $sun = dayPart::DAY;
        if (is_null($latitude) || is_null($longitude)) {
            return $sun;
        }

        $sunInfo = date_sun_info($time, $latitude, $longitude);

        $sec     = $time - mktime(0,0,0); //прошло секунд с начала суток
        $secRise = $sunInfo['sunrise'] - mktime(0,0,0); //секунд с даты восхода
        $secSet  = $sunInfo['sunset'] - mktime(0,0,0); // секунд с даты заката


        if (($sec<=$secRise) && ($sec>=$secRise-twilight::TWILIGHT_M)) {
            $sun = dayPart::MORNING;
        }
        elseif ($sec>$secRise && $sec<=$secSet) {
            $sun = dayPart::DAY;
        }
        elseif (($sec>$secSet) && ($sec<=$secSet+twilight::TWILIGHT_E)) {
            $sun = dayPart::EVENING;
        }
        else {
            $sun = dayPart::NIGHT;
        }

        return $sun;

    }

}

