<?php

require_once(dirname(__FILE__) . '/class/sqlDataBase.class.php');

class widgetWeather
{
    const cache_lifetime = 7200; //время кэша файла в секундах, 3600=1 час

    private static $city_id = null; //id города, вписать свой, можно узнать тут https://pogoda.yandex.ru/static/cities.xml - параметр city id=
    private static $cache_file = null; // временный файл-кэш
    private static $url = null; // путь до файла xml в интернете

    static private function init()
    {
        if (is_null(self::$city_id)) {
            $cityId = DB::getConst('WeatherCityId');
            self::$city_id = $cityId;
        }
        if (is_null(self::$cache_file)) self::$cache_file = 'cache/weather_' . self::$city_id . '.xml';
        if (is_null(self::$url)) self::$url = 'http://informer.gismeteo.ru/xml/' . self::$city_id . '.xml';
    }

    static private function loadYandexXML()
    {
        $userAgent = 'Googlebot/2.1 (+https://www.google.com/bot.html)';
        $ch = curl_init(self::$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $output = curl_exec($ch);
        $fh = fopen(self::$cache_file, 'w');
        fwrite($fh, $output);
        fclose($fh);
    }

    static private function checkFile()
    {
        if (file_exists(self::$cache_file)) {

            try {
                $cache_modified = time() - filemtime(self::$cache_file);
            }
            catch (Exception $e) {
                $cache_modified = 0;
            }
            if ($cache_modified > self::cache_lifetime) //обновляем файл погоды, если время файла кэша устарело
            {
                $check_url = get_headers(self::$url);
                $ok = 'Connection: close';
                if (!(strpos($check_url[3], $ok) === false)) {
                    self::loadYandexXML();
                }
            }
        }
        else {
            self::loadYandexXML();
        }
    }

    /**
     * Возвращает массив с данными погоды
     */
    static public function getWeather()
    {
        self::init();
        self::checkFile();

        $data = null;
        if (file_exists(self::$cache_file)) {
            $data = simplexml_load_file(self::$cache_file);
        }

        $aResult = [];

        foreach ($data->REPORT->TOWN->FORECAST as $forecast) {

            $tod = intval($forecast['tod']);
            $temp_min = intval($forecast->TEMPERATURE['min']);
            $temp_max = intval($forecast->TEMPERATURE['max']);
            $precipitation = intval($forecast->PHENOMENA['precipitation']);
            $rainPower = intval($forecast->PHENOMENA['rainPower']);
            $snowPower = intval($forecast->PHENOMENA['snowPower']);
            $cloudiness = intval($forecast->PHENOMENA['cloudiness']);
            $pressure_min = intval($forecast->PRESSURE['min']);
            $pressure_max = intval($forecast->PRESSURE['max']);
            $wind_min = intval($forecast->WIND['min']);
            $wind_max = intval($forecast->WIND['max']);
            $wind_dir = intval($forecast->WIND['direction']);
            $relwet_min = intval($forecast->RELWET['min']);
            $relwet_max = intval($forecast->RELWET['max']);

            $temp = round(($temp_min + $temp_max) / 2);
            $temp_class = $temp > 0 ? 'temp_plus' : 'temp_minus';
            $temp_ = ($temp > 0) ? '+' . $temp : $temp;
            $pressure = round(($pressure_min + $pressure_max) / 2);
            $wind = round(($wind_min + $wind_max) / 2);
            $relwet = round(($relwet_min + $relwet_max) / 2);

            $aTek = [
                'temp' => $temp_,
                'temp_class' => $temp_class,
                'tod' => $tod,
                'precipitation' => $precipitation,
                'cloudiness' => $cloudiness,
                'rainPower' => $rainPower,
                'snowPower' => $snowPower,
                'pressure' => $pressure,
                'wind' => $wind,
                'wind_dir' => $wind_dir,
                'relwet' => $relwet
            ];
            $aResult[] = $aTek;
        }
        return $aResult;
    }

    /**
     * Получить рисунок отображающий погоду
     * @param $tod - часть суток: 0 - Ночь, 1 - Утро, 2 - День, 3 - Вечер
     * @param $precipitation - осадки: 4 5 - дождь, 6 7 - снег, 8 - гроза, 9 10 - ясно
     * @param $cloudiness - облачность: 0 - ясно, 1 - малооблачно, 2 - облачно, 3 - пасмурно
     * @param $rainPower - интенсивность дождя
     * @param $snowPower - интенсивность снега
     * @return string - путь до картинки
     */
    static public function getImageURL($tod, $precipitation, $cloudiness, $rainPower, $snowPower)
    {
        $myRow = (!$tod ? 'n' : 'd'); // день или ночь

        if ($cloudiness > 0) { // есть облачность
            if ($cloudiness >= 3) { // если облачность 3, то знак день/ночь не нужны
                $myRow = 'c3';
            }
            else {
                $myRow = $myRow . '_c' . $cloudiness;
            }
        }

        if ($precipitation == 4 || $precipitation == 5) { //дождь
            $rainPower = !$rainPower ? 1 : $rainPower;
            $rainPower = min($rainPower, 3);
            $myRow = $myRow . '_r' . $rainPower;
        }

        if ($precipitation == 6 || $precipitation == 7) { //снег
            $snowPower = !$snowPower ? 1 : $snowPower;
            $snowPower = min($snowPower, 3);
            $myRow = $myRow . '_s' . $snowPower;
        }

        if ($precipitation == 8) { //гроза
            $myRow = $myRow . '_st';
        }

        $bk = "background:url(img2/weather/$myRow.png) no-repeat scroll 0 0 transparent";

        return "<div class='$myRow' style='$bk;height:55px;width:55px'></div>";
    }

    static public function get_wind($wind_dir)
    {
        switch ($wind_dir) {
            case 1 :
                $w = 'С';
                break;
            case 2 :
                $w = 'СВ';
                break;
            case 3 :
                $w = 'В';
                break;
            case 4 :
                $w = 'ЮВ';
                break;
            case 5 :
                $w = 'Ю';
                break;
            case 6 :
                $w = 'ЮЗ';
                break;
            case 7 :
                $w = 'З';
                break;
            case 8 :
                $w = 'СЗ';
                break;
            default :
                $w = '';
        }
        return $w;
    }

    static public function getDayPart($part) {
        $dayPart = '';
        switch ($part) {
            case 0 :
                $dayPart = 'ночь';
                break;
            case 1 :
                $dayPart = 'утро';
                break;
            case 2 :
                $dayPart = 'день';
                break;
            case 3 :
                $dayPart = 'вечер';
                break;
        }
        return $dayPart;
    }
}

?>

<style>
    /*noinspection CssUnusedSymbol*/
    .temp_plus {
        color: #ffb687;
    }

    /*noinspection CssUnusedSymbol*/
    .temp_minus {
        color: #caffff;
    }

    div.w_line {
        line-height: 1.2
    }
</style>

<div class="weather_home" style="width:800px">
    <?php

    $w = widgetWeather::getWeather();

    $temp_class = $w[0]['temp_class'];
    $temp = $w[0]['temp'];
    $img = widgetWeather::getImageURL($w[0]['tod'],
        $w[0]['precipitation'],
        $w[0]['cloudiness'],
        $w[0]['rainPower'],
        $w[0]['snowPower']);
    $pressure = $w[0]['pressure'];
    $wind = $w[0]['wind'];
    $wind_dir = widgetWeather::get_wind($w[0]['wind_dir']);
    $relwet = $w[0]['relwet'];
    ?>

    <div class='temp_now' style='width:200px;float:left;margin-left:5px'>
        <h3 class="Title1">Прогноз погоды</h3>
        <div class='w_line' style='color:#DDDDDD;float:left;font-size:85%'>
            <p>давление: <?php echo "$pressure" ?> мм</p>
            <p>ветер: <?php echo "$wind" ?> м/с <?php echo "$wind_dir" ?></p>
            <p>влажность: <?php echo "$relwet" ?>%</p>
        </div>
    </div>

    <?php
    for ($i = 0; $i <= 3; $i++) {
        $temp_class = $w[$i]['temp_class'];
        $temp = $w[$i]['temp'];
        $img = widgetWeather::getImageURL($w[$i]['tod'],
            $w[$i]['precipitation'],
            $w[$i]['cloudiness'],
            $w[$i]['rainPower'],
            $w[$i]['snowPower']);
        $dayPart = widgetWeather::getDayPart($w[$i]['tod']);
        ?>

        <div class='temp_other' style='width:140px;float:left;display:block'>
            <div style='width:140px;float:left;font-size:80%'><?php echo "$dayPart" ?></div>
            <div style="display:inline;">
                <div style="float:left;margin-top: 5px;"><?php echo "$img" ?></div>
                <div class='<?php echo "$temp_class" ?>' style='float:left;margin-top:5px;margin-left:5px;font-size:160%'> <?php echo "$temp" ?>&deg</div>
            </div>
        </div>

        <?php
    }
    ?>

</div>
