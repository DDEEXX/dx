<script src="js2/global.js"></script>

<?php
$p = isset($_REQUEST['p']) ? $_REQUEST['p'] : 'home';

$alarm = 'off'; //Это затычка

if ($alarm != 'off') {
    $p = 'alarm';
}

if ($p == 'alarm') {
    ?>

    <script>
        $(function () {

            var $alarm_p = $('#alarm_p'),
                $alarm_pass = $('#alarm_pass');

            $alarm_pass.val("");

            $('.alarm_button').button().click(function () {

                var $this = $(this),
                    character = $this.attr("letter");

                if ($this.hasClass('alarm_button_delete')) {
                    var html = $alarm_p.html(),
                        value = $alarm_pass.val();
                    $alarm_p.html(html.substr(0, html.length - 2));
                    $alarm_pass.val(value.substr(0, value.length - 1));
                    return false;
                }

                if ($this.hasClass('alarm_button_ok')) {
                    var val = $alarm_pass.val();
                    $.get("alarm.php?p=off&pp=" + val, function () {
                    });
                    location.reload(true);
                    return false;
                }

                if ($alarm_pass.val().length < 5) {
                    $alarm_p.html($alarm_p.html() + '* ');
                    $alarm_pass.val($alarm_pass.val() + character);
                }

            });

        });
    </script>

    <div id="page_alarm" class="grid_11">
        <div class="grid_11 alpha ui-corner-all ui-widget-header" style="margin-top: 5px">
            <h2 style="margin-left:5px;font-size:150%;">Введите код снятия с сигнализации</h2>
        </div>
        <div class="clear"></div>
        <div class="grid_11 alpha">
            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:90px;border:0">
            </div>
            <div class="grid_9 ui-corner-all ui-state-default" style="margin-top:5px;height:90px;width:698px">
                <h2 id="alarm_p" style="font-size:80px;text-align:center"></h2>
                <input id="alarm_pass" type="hidden"/>
            </div>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:90px;border:0">
            </div>
            <div class="clear"></div>

            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="1">
                <h2 style="font-size:110px">1</h2>
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="2">
                <h2 style="font-size:110px">2</h2>
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="3">
                <h2 style="font-size:110px">3</h2>
            </div>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="clear"></div>

            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="4">
                <h2 style="font-size:110px">4</h2>
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="5">
                <h2 style="font-size:110px">5</h2>
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="6">
                <h2 style="font-size:110px">6</h2>
            </div>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="clear"></div>

            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="7">
                <h2 style="font-size:110px">7</h2>
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="8">
                <h2 style="font-size:110px">8</h2>
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="9">
                <h2 style="font-size:110px">9</h2>
            </div>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="clear"></div>

            <div class="grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="grid_3 alarm_button alarm_button_delete ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px">
                <h2 style="font-size:110px;color:yellow"><</h2>
            </div>
            <div class="grid_3 alarm_button ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px" letter="0">
                <h2 style="font-size:110px">0</h2>
            </div>
            <div class="grid_3 alarm_button alarm_button_ok ui-corner-all ui-state-default"
                 style="margin-top:5px;height:130px;width:218px">
                <h2 style="font-size:110px;color:green">OK</h2>
            </div>
            <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0">
            </div>
            <div class="clear"></div>


        </div>
    </div>

    <?php
}

if ($p == 'home' || empty($p)) {
    ?>

    <link rel="stylesheet" type="text/css" href="css2/style_home.css">
    <script src="js2/home.js"></script>

    <div id="page_home" class="grid_12" style="height: 790px">
        <div class="grid_12" style="height: 70px">
            <div class="grid_9 alpha">1</div>
            <div class="grid_2 omega">
                <div id="TekDate" class="TekDate" style="font-size:110%"></div>
                <br>
                <div id="TekTime" class="TekTime" style="font-size:160%"></div>
            </div>
            <div class="grid_1 alpha">1
                <a href="/?action=out">Выход</a>
            </div>
            <div class="clear"></div>
        </div>

        <div class="grid_5 alpha">
            <div id="home_cam" class="ui-corner-all ui-state-default" style="text-align: center; min-height: 276px">
                <img src="http://192.168.1.4:8081/" alt="http://192.168.1.4:8081/" style="margin: 5px; height: 266px; width: 470px">
                <div id="home_cameraFullSize" style="padding: 0"></div>
            </div>
        </div>
        <div class="grid_3">.</div>
        <div class="grid_4 omega">
            <div id="home_outdoor_sensors_block" class="ui-corner-all ui-state-default" style="display: flex; flex-direction: column; height: 154px">
                <h2 class="title_sensor">На улице</h2>
                <div id="home_outdoor_sensors" style="align-self: stretch; flex-grow: 1; overflow: auto">
                    <div class="block_sensor_data" style="margin-top: 20px">
                        <div id="home_sensor_temperature_out" class="home_weather_sensor short sensor_block"></div>
                        <div id="home_sensor_pressure" class="home_weather_sensor long sensor_block"></div>
                    </div>
                    <div class="block_sensor_data" style="margin-top: 20px">
                        <div id="home_sensor_humidity_out" class="home_weather_sensor short sensor_block"></div>
                        <div id="home_sensor_wind" class="home_weather_sensor long sensor_block"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear"></div>


<!--                <div id="alarm_key" class="grid_2 alpha ui-corner-all ui-state-default"-->
<!--                     style="margin-top:5px;height:100px;width:138px">-->
<!--                    <h2 style="margin-left:5px">Охрана</h2>-->
<!--                    <div style="text-align:center;margin-top:5px">-->
<!--                        <img src="img2/icon/lock a.png">-->
<!--                    </div>-->
<!--                </div>-->

    </div>

    <?php
}

if ($p == 'weather') {
    ?>

    <link rel="stylesheet" type="text/css" href="css2/style_weather.css">
    <script src="js2/weather.js"></script>

    <div id="page_weather" class="grid_12">
        <div class="grid_12 alpha">
            <div class="title_page ui-corner-all ui-widget-header">
                <h2>Погода</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_8 alpha">
            <!-- Прогноз погоды -->
            <div class="grid_8 alpha">
                <div id="weather_forecast" class="ui-corner-all ui-state-default" style="height:78px;margin-top:5px"></div>
            </div>
            <div class="clear"></div>
            <!-- Управление -->
            <div class="grid_8 alpha">
                <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 69px">
                    <div style="height: 55px; margin-top: 7px; margin-bottom: 7px">
                        <button id="weather_button_123" class="weather_button_setup"></button>
<!--                        <button id="weather_button_graph" class="weather_button_setup"></button>-->
                        <button id="weather_button_plan" class="weather_button_setup"></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid_4 omega">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 154px">
                <h2 class="title_sensor">На улице</h2>
                <div id="weather_outdoor_sensors">
                    <div class="block_sensor_data" style="margin-top: 20px">
                        <div id="weather_sensor_temperature_out" class="weather_sensor short sensor_block"></div>
                        <div id="weather_sensor_pressure" class="weather_sensor long sensor_block"></div>
                    </div>
                    <div class="block_sensor_data" style="margin-top: 20px">
                        <div id="weather_sensor_humidity_out" class="weather_sensor short sensor_block"></div>
                        <div id="weather_sensor_wind" class="weather_sensor long sensor_block"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <!-- содержание -->
        <div id="weather_content">
        </div>
    </div>

    <?php
}

//Освещение
if ($p == 'light') {
    ?>

    <script src="js2/light.js"></script>

        <div id="page_light" class="grid_11">

            <div class="grid_11 alpha">
                <div class="ui-corner-all ui-widget-header" style="margin-top: 5px">
                    <h2 style="margin-left:5px;font-size:150%;">Освещение</h2>
                </div>
            </div>
            <div class="clear"></div>

            <div class="grid_3 alpha">
                <div class="ui-corner-all ui-state-default" style="margin-top:5px;height:80px">
                </div>
            </div>
            <div class="clear"></div>

            <div id="accordion_light">
                <h3 class='dx'>План</h3>
                <div style="padding:0;border:0;overflow:visible">
                    <div class="grid_11 alpha">
                        <div class="ui-corner-all ui-state-default ui-widget-content"
                             style="height: 500px;margin-top:5px;position:relative;">
                            <div id="home_light"><img src="img2/home_.png" alt="img2/home_.png">
                                <div id="light_lamp2"></div>
                                <div class='lampkey' label='light_hol_2_n' style='top:250px;left:635px'></div>
                                <div id="light_lamp3"></div>
                                <div class='lampkey' label='light_stairs_3' style='top:220px;left:685px'></div>
                                <div id="light_lamp10"></div>
                                <div class='lampkey' label='bathroom_mirror_light' style='top:295px;left:735px'></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <h3 class='dx'>Управление</h3>
                <div style="padding:0;border:0;overflow:visible">
                    <div class="grid_11 alpha">
                        <div class="ui-corner-all ui-state-default" style="margin-top:5px;height:50px">
                            <div id="backlight_first_floor" class="backlight">
                                <div style="float: left; margin-left:5px;height:40px;">
                                    <h2>Подсветка 1 этаж</h2>
                                </div>
                                <div style="float: left; margin-left:25px;">
                                    <button class="button" style="width:70px;" mqtt="on">ВКЛ</button>
                                </div>
                                <div style="margin-left:25px;float:left">
                                    <button class="button" style="width:50px;float:left" mqtt="8"><img src="img2/light_min.png" alt="max"></button>
                                    <div class="light_level" style="height:38px;float:left;margin-left:1px">
                                        <button class="button" style="width:40px;margin-left:0px" mqtt="1"><img src="img2/light_1.png" alt="1"></button>
                                        <button class="button" style="width:40px;margin-left:-8px" mqtt="2"><img src="img2/light_2.png" alt="2"></button>
                                        <button class="button" style="width:40px;margin-left:-8px" mqtt="3"><img src="img2/light_3.png" alt="3"></button>
                                        <button class="button" style="width:40px;margin-left:-8px" mqtt="4"><img src="img2/light_4.png" alt="4"></button>
                                        <button class="button" style="width:40px;margin-left:-8px" mqtt="5"><img src="img2/light_5.png" alt="5"></button>
                                        <button class="button" style="width:40px;margin-left:-8px" mqtt="6"><img src="img2/light_6.png" alt="6"></button>
                                        <button class="button" style="width:40px;margin-left:-8px" mqtt="7"><img src="img2/light_7.png" alt="7"></button>
                                    </div>
                                    <button class="button" style="width:50px;margin-left:2px;float:left" mqtt="9"><img src="img2/light_max.png" alt="max"></button>
                                </div>
                                <div style="float: left; margin-left:25px;">
                                    <button class="button" style="width:70px;" mqtt="off">ВЫКЛ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

    <?php
}

if ($p == 'power') {
    ?>

    <script src="js2/power.js"></script>

    <div id="page_power" class="grid_11">
        <div class="grid_11 alpha">
            <div class="ui-corner-all ui-widget-header" style="margin-top: 5px">
                <h2 style="margin-left:5px;font-size:150%;">Исполнители</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_4 alpha">
            <div class="ui-corner-all ui-state-default ui-widget-content"
                 style="margin-top:5px;height:90px;position:relative">
                <h2 style="margin-left:5px">Гаражные ворота</h2>
                <div id="label_garage_door" style="float:left;margin-left:8px;margin-top:2px"></div>
                <button style="margin-left:20px;margin-top:10px;" class="upDown"></button>
            </div>
        </div>
        <div class="grid_4">
            <div class="ui-corner-all ui-state-default ui-widget-content"
                 style="margin-top:5px;height:90px;position:relative">
                <h2 style="margin-left:5px">Ворота</h2>
            </div>
        </div>
        <div class="grid_3 omega">
            <div class="ui-corner-all ui-state-default ui-widget-content"
                 style="margin-top:5px;height:90px;position:relative">
                <h2 style="margin-left:5px">Калитка</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_4 alpha">
            <div class="ui-corner-all ui-state-default ui-widget-content"
                 style="margin-top:5px;height:120px;position:relative">
                <div>
                    <h2 style="margin-left:5px">Погреб</h2>
                    <?php echoRadioGroup('rg_g_vault', 'vault_vent', 'vault_off', 'vault_on', 'vault_auto') ?>
                    <!--                    <div class="rg_g_vault" style="margin-left:5px;float:left">-->
                    <!--                        <input type="radio" name="1" dev_type="temp_out_1"-->
                    <!--                               id="vault_off"><label for="vault_off">выкл</label>-->
                    <!--                        <input type="radio" name="1" dev_type="temp_out_1"-->
                    <!--                               id="vault_on"><label for="vault_on">вкл</label>-->
                    <!--                        <input type="radio" name="1" dev_type="temp_out_1"-->
                    <!--                               id="vault_auto"><label for="vault_auto">авто</label>-->
                    <!--                    </div>-->
                </div>
                <p>температура: &deg </p>
                <p>влажность: %</p>
                <p>вентиляция: </p>
                <p>свет: </p>
            </div>
        </div>
    </div>
    <?php
}

if ($p == 'heater') {
    ?>
    <link rel="stylesheet" type="text/css" href="css2/style_heater.css">
    <script src="js2/heater.js"></script>

    <div id="page_heater" class="grid_12" style="display: flex; flex-direction: column; align-content: flex-start; height: 790px">
        <div class="grid_12 alpha omega" style="align-self: flex-start">
            <div class="title_page ui-corner-all ui-widget-header">
                <h2 style="margin-left:5px;font-size:150%;">Отопление и климат</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_12 alpha omega" style="align-self: flex-start">
            <div class="ui-corner-all ui-state-default ui-widget-content" style="height: 250px">
                <span>резерв</span>
            </div>
        </div>
        <div class="clear"></div>
        <div id="heater_data" class="grid_12 alpha omega" style="align-self: stretch; margin-top: 5px; margin-bottom: 2px; flex-grow: 1">
            <div class="grid_6 alpha" style="height: 100%">
                <div class="ui-corner-all ui-state-default ui-widget-content" style="height: 100%">
                    <div><img src="img2/heater.png" style="position: absolute; margin-left: 15px;margin-top: 30px"></div>

                    <div id="heater_temp_boiler_out" class="sensor_block temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:8px; top:335px; padding: 5px">
                        <div id="heater_sensor_temp_boiler_out" class="heater_sensor_schema"></div>
                    </div>
                    <div id="heater_temp_boiler_in" class="sensor_block temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:85px; top:335px; padding: 5px">
                        <div id="heater_sensor_temp_boiler_in" class="heater_sensor_schema"></div>
                    </div>
                    <div id="heater_temp_boiler_delta" class="temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:50px; top:380px; display: flex; align-items: flex-end; padding: 5px">
                        <span style="padding-right: 5px">&#916</span>
                        <div id="heater_temp_boiler_delta_data"></div>
                        <span style="margin-left: 2px">&deg</span>
                    </div>
                    <div id="heater_temp_floor_in" class="sensor_block temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:320px; top:330px; display: flex; align-items: flex-end; padding: 5px">
                        <div id="heater_sensor_temp_floor_in" class="heater_sensor_schema"></div>
                    </div>
                    <div id="heater_temp_floor_out" class="sensor_block temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:320px; top:420px; display: flex; align-items: flex-end; padding: 5px">
                        <div id="heater_sensor_temp_floor_out" class="heater_sensor_schema"></div>
                    </div>
                    <div id="heater_temp_floor_delta" class="temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:320px; top:375px; display: flex; align-items: flex-end; padding: 5px">
                        <span style="padding-right: 5px">&#916</span>
                        <div id="heater_temp_floor_delta_data"></div>
                        <span style="margin-left: 2px">&deg</span>
                    </div>
                    <div id="heater_temp_sauna_out" class="sensor_block temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:150px; top:125px; display: flex; flex-direction: column; padding: 5px">
                            <div>Баня</div>
                            <div id="heater_sensor_temp_sauna_out" class="heater_sensor_schema"></div>
                    </div>
                    <div id="heater_temp_floor1_out" class="sensor_block temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:210px; top:195px; display: flex; flex-direction: column; padding: 5px">
                        <div>1 этаж</div>
                        <div id="heater_sensor_temp_floor1_out" class="heater_sensor_schema"></div>
                    </div>
                    <div id="heater_temp_floor2_out" class="sensor_block temp_scheme ui-corner-all ui-state-default ui-widget-content"
                         style="position:absolute; left:270px; top:265px; display: flex; flex-direction: column; padding: 5px">
                        <div>2 этаж</div>
                        <div id="heater_sensor_temp_floor2_out" class="heater_sensor_schema"></div>
                    </div>
                    <div id="heater_widget"
                         style="position:absolute; left:270px; top:5px; width:300px; height:130px;">
                    </div>
                </div>
            </div>
            <div class="grid_6 omega" style="height: 100%">
                <div class="ui-corner-all ui-state-default ui-widget-content" style="height: 100%">
                    <span>резерв</span>
                </div>
            </div>
            <div class="clear"></div>

<!--            <div class="ui-corner-all ui-state-default ui-widget-content" style="height: 100%">-->
<!--                <span>резерв</span>-->
<!--                <div><img src="img2/home_.png" style="margin-left: 65px;margin-top: 5px"></div>-->
<!--            </div>-->
        </div>
    </div>

    <?php
}

if ($p == 'cam') {

    ?>

    <link rel="stylesheet" type="text/css" href="css2/style_cameras.css">
    <script src="js2/cameras.js"></script>

    <div id="page_cameras" class="grid_12" style="display: flex; flex-direction: column; align-content: flex-start; height: 790px">
        <div class="grid_12 alpha omega" style="align-self: flex-start">
            <div class="title_page ui-corner-all ui-widget-header">
                <h2>Видеонаблюдение</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div id="cam_data" class="grid_12 alpha omega" style="align-self: stretch; margin-top: 5px; margin-bottom: 2px; flex-grow: 1">
            <div class="grid_4 alpha">
                <div class="ui-corner-all ui-state-default ui-widget-content" style="min-height: 255px">
                    <div id="cam_monitor_1" class="camera_monitor">
                        <img src="http://192.168.1.4:8081/" alt="http://192.168.1.4:8081/" style="width:375px;height:213px">
                    </div>
                    <div id="cam_Monitor_1_full_size" style="padding: 0"></div>
                    <button id="cam_archive_1" class="cam_button" style="margin: 5px">Архив</button>
                </div>
            </div>
            <div class="grid_4">
                <div class="ui-corner-all ui-state-default ui-widget-content">
                --
                </div>
            </div>
            <div class="grid_4 omega">
                <div class="ui-corner-all ui-state-default ui-widget-content">
                --
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>

    <?php
}

if ($p == 'properties') {
    ?>

    <script src="js2/properties.js"></script>

<div id="page_properties" class="grid_11">

    <div class="grid_11 alpha omega ui-corner-all ui-state-default">
            <div class="logger" style="margin-left:5px;margin-top:5px">
                <input type="radio" name="logger" id="logDefault"">
                <label for="logDefault">Default</label>
                <input type="radio" name="logger" id="logError" checked>
                <label for="logError">Error</label>
            </div>
    </div>
    <div class="clear"></div>

</div>
    <?php
}
?>
