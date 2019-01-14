<style>

.skc_d {
    background:url(/img2/weather/skc_d.png) no-repeat scroll 0 0 transparent;
}

.temp_plus {
	color : #ffb687;
}

.temp_minus {
	color : #caffff;
}

div.w_line {
	line-height: 1.2
}

</style>

<?php

$city_id = 28925; //id города, вписать свой, можно узнать тут https://pogoda.yandex.ru/static/cities.xml - параметр city id=
$cache_lifetime = 7200; //время кэша файла в секундах, 3600=1 час
$cache_file = 'weather_'.$city_id.'.xml'; // временный файл-кэш 
$url = 'http://informer.gismeteo.ru/xml/'.$city_id.'.xml';	

function loadxmlyansex($city_id, $url) {
	$userAgent = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
	$xml = 'weather_'.$city_id.'.xml';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	$output = curl_exec($ch);
	$fh = fopen($xml, 'w');
	fwrite($fh, $output);
	fclose($fh);
}

function get_img($tod, $precipitation, $cloudiness, $rpower, $spower) {
	
	//$tod: 0 - Ночь, 1 - Утро, 2 - День, 3 - Вечер
	//$cloudiness: 0 - ясно, 1 - малооблачно, 2 - облачно, 3 - пасмурно
	//$precipitation: 4 5 - дождь, 6 7 - снег, 8 - гроза, 9 10 - ясно
	//$rpower = интенсивность дождя
	//$spower = интенсивность снега
	
	$myrow = (!$tod?"n":"d"); // день или ночь
	
	if ($cloudiness>0) { // есть облочность
		if ($cloudiness>=3) { // если облачность 3 то знак день/ночь не нужны
			$myrow = "c3";
		}
		else {
			$myrow = $myrow."_c".$cloudiness;
		}
	}
	
	if ($precipitation == 4 || $precipitation == 5) { //дождь
		$rpower = !$rpower?1:$rpower;
		$rpower = $rpower>3?3:$rpower;
		$myrow = $myrow."_r".$rpower;
	}
	
	if ($precipitation == 6 || $precipitation == 7) { //снег
		$spower = !$spower?1:$spower;
		$spower = $spower>3?3:$spower;
		$myrow = $myrow."_s".$rpower;
	}
	
	if ($precipitation == 8) { //гроза
		$myrow = $myrow."_st";
	}
  	
  	$bk = "background:url(/img2/weather/$myrow.png) no-repeat scroll 0 0 transparent";
  	
  	return "<div class='$myrow' style='$bk;height:55px;width:55px'></div>";
  	
}

function get_wind($wind_dir) {
	
	switch ($wind_dir) {
		case 1 : $w = "С"; break;
		case 2 : $w = "СВ"; break;
		case 3 : $w = "В"; break;
		case 4 : $w = "ЮВ"; break;
		case 5 : $w = "Ю"; break;
		case 6 : $w = "ЮЗ"; break;
		case 7 : $w = "З"; break;
		case 8 : $w = "СЗ"; break;
		default : $w = "";
	}
	
	return $w;
	
}

if ( file_exists($cache_file) ) {
	
	$cache_modified = time() - @filemtime($cache_file);
	if ( $cache_modified > $cache_lifetime ) {//обновляем файл погоды, если время файла кэша устарело
		$check_url = get_headers($url);
		$ok = 'application/xml';
		if (strpos($check_url[3],$ok)){
			loadxmlyansex($city_id, $url);
		}
	}
}
else {
	loadxmlyansex($city_id, $url);
}

if (file_exists($cache_file)) {
	$data = simplexml_load_file($cache_file); 
}
?>

<div class = "weather_home" style="width:680px"> 

<?php	
$i = 1;

foreach ($data->REPORT->TOWN->FORECAST as $forecast ){
	
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
	
	
	if ($i==1) { //первый проход
		
		$temp = round(($temp_min+$temp_max)/2);
		$temp_class = $temp>0?"temp_plus":"temp_minus";
		$temp_ = ($temp > 0)? '+'.$temp:$temp;
		$pressure = round(($pressure_min+$pressure_max)/2);
		$wind = round(($wind_min+$wind_max)/2);
		$wind_ = (!$wind)?'штиль':$wind;
		$relwet = round(($relwet_min+$relwet_max)/2);
		
		echo "<div class='temp_now' style='width:260px;float:left'>"; 
		echo 	"<div class='$temp_class' style='float:right;font-size:200%'>".$temp_."&deg</div>"; 
		echo 	"<div style='float:right'>".get_img($tod, $precipitation, $cloudiness, $rpower, $spower)."</div>"; 
		echo 	"<div class = 'w_line' style='color:#DDDDDD;float:right;font-size:70%'>"; 
		echo 		"<p>давление: $pressure мм</p>"; 
		echo 		"<p>ветер: $wind м/с ".get_wind($wind_dir)."</p>"; 
		echo 		"<p>влажность: $relwet %</p>"; 
		echo 	"</div>"; 
		echo "</div>"; 
	}
	else {
		
		$temp = round(($temp_min+$temp_max)/2);
		$temp_class = $temp>0?"temp_plus":"temp_minus";
		$temp_ = ($temp > 0)? '+'.$temp:$temp;
		
		echo "<div class='temp_$tod' style='width:120px;float:left'>"; 
		echo 	"<div class='$temp_class' style='float:right;font-size:200%'>".$temp_."&deg</div>"; 
		echo 	"<div style='float:right'>".get_img($tod, $precipitation, $cloudiness, $rpower, $spower)."</div>"; 
		echo "</div>"; 
	}
	
	$i++;
}	

echo "</div>"; 
	
?>

</div>
