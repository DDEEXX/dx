<?php

require_once(dirname(__FILE__) . "/class/sqlDataBase.class.php");

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

    static private function loadxmlyansex()
    {
        $userAgent = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
        $ch = curl_init(self::$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $output = curl_exec($ch);
        $fh = fopen(self::$cache_file, 'w');
        fwrite($fh, $output);
        fclose($fh);
    }

    static private function checkFile()
    {
        if (file_exists(self::$cache_file)) {

            $cache_modified = time() - @filemtime(self::$cache_file);
            if ($cache_modified > self::cache_lifetime) //обновляем файл погоды, если время файла кэша устарело
            {
                $check_url = get_headers(self::$url);
                $ok = 'application/xml';
                $ok = 'Connection: close';
                if (!(strpos($check_url[3], $ok) === false)) {
                    self::loadxmlyansex();
                }
            }
        }
        else {
            self::loadxmlyansex();
        }
    }

    /**
     * Возвращает массив с данными погоды
     */
    static public function getWether()
    {

        self::init();
        self::checkFile();

        $data = null;
        if (file_exists(self::$cache_file)) {
            $data = simplexml_load_file(self::$cache_file);
        }

        $aResult = array();

        foreach ($data->REPORT->TOWN->FORECAST as $forecast) {

            $tod = intval($forecast['tod']);
            $temp_min = intval($forecast->TEMPERATURE['min']);
            $temp_max = intval($forecast->TEMPERATURE['max']);
            $precipitation = intval($forecast->PHENOMENA['precipitation']);
            $rpower = intval($forecast->PHENOMENA['rpower']);
            $spower = intval($forecast->PHENOMENA['spower']);
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

            $aTek = array(
                'temp' => $temp_,
                'temp_class' => $temp_class,
                'tod' => $tod,
                'precipitation' => $precipitation,
                'cloudiness' => $cloudiness,
                'rpower' => $rpower,
                'spower' => $spower,
                'pressure' => $pressure,
                'wind' => $wind,
                'wind_dir' => $wind_dir,
                'relwet' => $relwet
            );
            $aResult[] = $aTek;
        }
        return $aResult;
    }

    static public function get_img($tod, $precipitation, $cloudiness, $rpower, $spower)
    {

        //$tod: 0 - Ночь, 1 - Утро, 2 - День, 3 - Вечер
        //$cloudiness: 0 - ясно, 1 - малооблачно, 2 - облачно, 3 - пасмурно
        //$precipitation: 4 5 - дождь, 6 7 - снег, 8 - гроза, 9 10 - ясно
        //$rpower = интенсивность дождя
        //$spower = интенсивность снега

        $myrow = (!$tod ? "n" : "d"); // день или ночь

        if ($cloudiness > 0) { // есть облочность
            if ($cloudiness >= 3) { // если облачность 3 то знак день/ночь не нужны
                $myrow = "c3";
            }
            else {
                $myrow = $myrow . "_c" . $cloudiness;
            }
        }

        if ($precipitation == 4 || $precipitation == 5) { //дождь
            $rpower = !$rpower ? 1 : $rpower;
            $rpower = $rpower > 3 ? 3 : $rpower;
            $myrow = $myrow . "_r" . $rpower;
        }

        if ($precipitation == 6 || $precipitation == 7) { //снег
            $spower = !$spower ? 1 : $spower;
            $spower = $spower > 3 ? 3 : $spower;
            $myrow = $myrow . "_s" . $spower;
        }

        if ($precipitation == 8) { //гроза
            $myrow = $myrow . "_st";
        }

        $bk = "background:url(img2/weather/$myrow.png) no-repeat scroll 0 0 transparent";

        return "<div class='$myrow' style='$bk;height:55px;width:55px'></div>";

    }

    static public function get_wind($wind_dir)
    {

        switch ($wind_dir) {
            case 1 :
                $w = "С";
                break;
            case 2 :
                $w = "СВ";
                break;
            case 3 :
                $w = "В";
                break;
            case 4 :
                $w = "ЮВ";
                break;
            case 5 :
                $w = "Ю";
                break;
            case 6 :
                $w = "ЮЗ";
                break;
            case 7 :
                $w = "З";
                break;
            case 8 :
                $w = "СЗ";
                break;
            default :
                $w = "";
        }

        return $w;

    }
}

?>

<style>
    .temp_plus {
        color: #ffb687;
    }

    .temp_minus {
        color: #caffff;
    }

    div.w_line {
        line-height: 1.2
    }
</style>

<div class="weather_home" style="width:680px">
    <?php
    $w = widgetWeather::getWether();

    $temp_class = $w[0]['temp_class'];
    $temp = $w[0]["temp"];
    $img = widgetWeather::get_img($w[0]["tod"],
        $w[0]["precipitation"],
        $w[0]["cloudiness"],
        $w[0]["rpower"],
        $w[0]["spower"]);
    $pressure = $w[0]["pressure"];
    $wind = $w[0]["wind"];
    $wind_dir = widgetWeather::get_wind($w[0]["wind_dir"]);
    $relwet = $w[0]["relwet"];

    $pd = '';
    switch ($w[0]["tod"]) {
        case 0 :
            $pd = "ночь";
            break;
        case 1 :
            $pd = "утро";
            break;
        case 2 :
            $pd = "день";
            break;
        case 3 :
            $pd = "вечер";
            break;
    }

    ?>

    <div class='temp_now' style='width:260px;float:left'>
        <div class='<?php echo "$temp_class" ?>' style='float:right;font-size:160%'> <?php echo "$temp" ?> &deg</div>
        <div style='float:right'><?php echo "$img" ?></div>
        <div class='w_line' style='color:#DDDDDD;float:right;font-size:70%'>
            <p>давление: <?php echo "$pressure" ?> мм</p>
            <p>ветер: <?php echo "$wind" ?> м/с <?php echo "$wind_dir" ?></p>
            <p>влажность: <?php echo "$relwet" ?> %</p>
            <p>прогноз на: <?php echo "$pd" ?> </p>
        </div>
    </div>

    <?php
    for ($i = 1; $i <= 3; $i++) {
        $temp_class = $w[$i]['temp_class'];
        $temp = $w[$i]["temp"];
        $img = widgetWeather::get_img($w[$i]["tod"],
            $w[$i]["precipitation"],
            $w[$i]["cloudiness"],
            $w[$i]["rpower"],
            $w[$i]["spower"]);
        ?>

        <div class='temp_other' style='width:130px;float:left'>
            <div class='<?php echo "$temp_class" ?>' style='float:right;font-size:160%'> <?php echo "$temp" ?>&deg
            </div>
            <div style='float:right'><?php echo "$img" ?></div>
        </div>

        <?php
    }
    ?>

</div>
