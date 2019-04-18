<?php

$f_json = 'http://192.168.0.201/temp';

$json = file_get_contents("$f_json");

var_dump(json_decode($json));

$f_json = 'http://192.168.0.201/hum';

$json = file_get_contents("$f_json");

$hum = json_decode($json);

var_dump(json_decode($json));


echo $hum->return_value/100;