<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 10.03.19
 * Time: 19:12
 */

require_once(dirname(__FILE__). '/sqlDataBase.class.php');
require_once(dirname(__FILE__). '/sharedMemory.class.php');

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

    /**
     * Определяет часть суток
     * @param $time - момент времени для определения
     * @return int - dayPart::MORNING - утро, dayPart::DAY - день, dayPart::EVENING - вечер, dayPart::NIGHT - ночь,
     */
    public static function getSunInfo($time) {

        $latitude = managerSharedMemory::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::LATITUDE);
        $longitude = managerSharedMemory::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::LONGITUDE);
        if (is_null($latitude) || is_null($longitude)) {
            return dayPart::DAY;
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

