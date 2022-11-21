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

    <script src="js2/home.js"></script>

    <div id="page_home" class="grid_11">
        <div class="grid_8 alpha">1
        </div>
        <div class="grid_2 omega">
            <div id="TekDate" class="TekDate" style="font-size:110%"></div>
            <br>
            <div id="TekTime" class="TekTime" style="font-size:160%"></div>
        </div>
        <div class="grid_1 alpha">1
            <a href="/?action=out">Выход</a>
        </div>
        <div class="clear"></div>

        <div class="grid_6 alpha">
            .
        </div>
        <div class="grid_5 alpha">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px">
                <h2 style="margin-left:5px">Погода на улице</h2>
                <div id="block_home_outdoor_data" style="display: table; margin: 5px">
                    <div style="height: 40px; width: 90px; display: table-cell">
                        <img style="margin-top:5px;float: left" src="img2/icon_medium/thermometer.png">
                        <div id="home_temperature_out" style="margin-top:10px;margin-left:5px;float: left"></div>
                    </div>
                    <div style="height: 40px; width: 90px; display: table-cell">
                        <img style="margin-top:5px;float: left" src="img2/icon_medium/barometer.png">
                        <div id="home_pressure" style="margin-top:10px;margin-left:5px;float: left"></div>
                    </div>
                    <div style="height: 40px; width: 90px; display: table-cell">
                        <img style="margin-top:5px;float: left" src="img2/icon_medium/humidity.png">
                        <div id="home_humidity_out" style="margin-top:10px;margin-left:5px;float: left"></div>
                    </div>
                    <div style="height: 40px; width: 100px; display: table-cell">
                        <img style="margin-top:5px;float: left" src="img2/icon_medium/wind.png">
                        <div id="home_wind" style="margin-top:10px;margin-left:5px;float: left"></div>
                    </div>
                </div>
            </div>
        </div>

        <!--
        <div class="grid_3 omega">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px">
                <p style="margin-left:3px; font-size:100%">Погода на улице</p>
                <div style="overflow: hidden">
                    <div style="margin-left:5px;float:left">t</div>
                    <div id="temp_out_weather_home" style="margin-left:30px;float:left">--</div>
                </div>
                <div style="overflow: hidden;position:relative">
                    <div style="margin-left:5px;float:left">p</div>
                    <div id="pressure_weather_home" style="margin-left:30px;float:left">--</div>
                    <div style="margin-left:85px;font-size:60%;position:absolute;bottom:2px"> мм рт.ст.</div>
                </div>
                <div style="overflow: hidden;position:relative">
                    <div style="margin-left:5px;float:left">w</div>
                    <div id="wind_weather_home" style="margin-left:30px;float:left">--</div>
                    <div style="margin-left:85px;font-size:60%;position:absolute;bottom:2px"> м/c</div>
                </div>
            </div>
        </div>
        -->
        <div class="clear"></div>


        <!--        <div id="alarm_key" class="grid_2 alpha ui-corner-all ui-state-default"-->
        <!--             style="margin-top:5px;height:100px;width:138px">-->
        <!--            <h2 style="margin-left:5px">Охрана</h2>-->
        <!--            <div style="text-align:center;margin-top:5px">-->
        <!--                <img src="img2/icon/lock a.png">-->
        <!--            </div>-->
        <!--        </div>-->

    </div>

    <?php
}

if ($p == 'weather') {
    ?>

    <script src="js2/weather.js"></script>

    <div id="page_weather" class="grid_11">
        <div class="grid_11 alpha">
            <div class="ui-corner-all ui-widget-header" style="margin-top: 5px">
                <h2 style="margin-left:5px;font-size:150%;">Погода</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_11 alpha">
            <div class="ui-corner-all ui-state-default" style="overflow:hidden;margin-top:5px">
                <div style="margin-left:5px">
                    <div id="weather_forecast"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_860_1 alpha">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">На улице</h2>
                <div id="block_weather_outdoor" isBlock="true">
                </div>
            </div>
        </div>
        <div class="grid_860_1">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Баня</h2>
            </div>
        </div>
        <div class="grid_860_1 omega">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Погреб</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_860_1 alpha">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Зал</h2>
                <div id="block_weather_hall" isBlock="true">
                </div>
            </div>
        </div>
        <div class="grid_860_1">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Кабинет</h2>
            </div>
        </div>
        <div class="grid_860_1 omega">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Сервер</h2>
                <div id="block_weather_server" isBlock="true">
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_860_1 alpha">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Спальня</h2>
                <div id="block_weather_bedroom" isBlock="true">
                </div>
            </div>
        </div>
        <div class="grid_860_1">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Спальня Леры</h2>
                <div id="block_weather_bedroom_Lera" isBlock="true">
                </div>
            </div>
        </div>
        <div class="grid_860_1 omega">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Ванная</h2>
                <div id="block_weather_bathroom" isBlock="true">
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_860_1 alpha">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px; height: 130px">
                <h2 style="margin-left:5px">Резерв</h2>
            </div>
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
                            <div id="home_light"><img src="img2/home_.png">
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
    <script src="js2/heater.js"></script>

    <div id="page_temp" class="grid_11">
        <div class="grid_11 alpha">
            <div class="ui-corner-all ui-widget-header" style="margin-top: 5px">
                <h2 style="margin-left:5px;font-size:150%;">Отопление и климат</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_3 alpha">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px;height:80px">
                <h2 style="margin-left:5px">На улице</h2>
                <img style="margin-top:5px;float:left" src="img2/temp.png">
                <div id="temp_out_1" style="margin-top:10px;"></div>
            </div>
        </div>
        <div class="grid_3 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:218px">
            <h2 style="margin-left:5px">Под лестницей</h2>
            <img style="margin-top:5px;float:left" src="img2/temp.png">
            <div id="temp_under_stair" style="margin-top:10px;"></div>
        </div>
        <div class="grid_3 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:218px">
        </div>
        <div class="grid_1 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:58px">
        </div>
        <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:58px">
        </div>
        <div class="clear"></div>

        <div id="accordion" style="margin-top:5px">
            <h3 class='dx'>План</h3>
            <div style="padding:0;border:0;overflow:visible">
                <div class="grid_11 alpha">
                    <div class="ui-corner-all ui-state-default ui-widget-content"
                         style="height: 500px;margin-top:5px;position:relative">
                        <div id="floor1"><img src="img2/home_.png"></div>
                        <div id="temp_hall" class="temp_plan ui-corner-all ui-state-default ui-widget-content"
                             style="position:absolute;left:100px;top:220px"></div>
                        <div id="temp_bedroom" class="temp_plan ui-corner-all ui-state-default ui-widget-content"
                             style="position:absolute;left:540px;top:310px"></div>
                        <div id="temp_bathroom" class="temp_plan ui-corner-all ui-state-default ui-widget-content"
                             style="position:absolute;left:750px;top:260px"></div>
                        <div id="temp_bedroom_Lera" class="temp_plan ui-corner-all ui-state-default ui-widget-content"
                             style="position:absolute;left:600px;top:190px"></div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <h3 class='dx'>Графики</h3>
            <div style="padding:0;border:0;overflow:visible">
                <div class="grid_5 alpha ui-corner-all ui-state-default"
                     style="margin-top:5px;height:215px;width:418px">
                    <h2 style="margin-left:5px;float:left">Температура на улице</h2>
                    <div id="temp_out_1_g" style="margin-right:5px;float:right"></div>
                    <div style="text-align: center">
                        <?php
                        echo '<img id="g_temp_out_1" src="graph.php?label=temp_out_1&t=line&date_from=day&' . rand() . '" height="160">';
                        ?>
                    </div>
                    <div class="rg_g_temp" style="margin-left:5px;float:left">
                        <input type="radio" name="period1" class="set_period" dev_type="temp_out_1" dev_period="day"
                               checked id="day_out1"><label for="day_out1">сут.</label>
                        <input type="radio" name="period1" class="set_period" dev_type="temp_out_1" dev_period="week"
                               id="week_out1"><label for="week_out1">нед.</label>
                        <input type="radio" name="period1" class="set_period" dev_type="temp_out_1" dev_period="month"
                               id="month_out1"><label for="month_out1">мес.</label>
                    </div>
                </div>

                <div class="grid_5 omega ui-corner-all ui-state-default"
                     style="margin-top:5px;height:215px;width:418px">
                    <h2 style="margin-left:5px;float:left">Температура 1 этаж</h2>
                    <div id="temp_hall_g" style="margin-right:5px;float:right"></div>
                    <div style="text-align: center">
                        <?php
                        echo '<img id="g_temp_hall" src="graph.php?label=temp_hall&t=line&date_from=day&' . rand() . '" height="160">';
                        ?>
                    </div>
                    <div class="rg_g_temp" style="margin-left:5px;float:left">
                        <input type="radio" name="period2" class="set_period" dev_type="temp_hall" dev_period="day"
                               checked id="day_hall"><label for="day_hall">сут.</label>
                        <input type="radio" name="period2" class="set_period" dev_type="temp_hall" dev_period="week"
                               id="week_hall"><label for="week_hall">нед.</label>
                        <input type="radio" name="period2" class="set_period" dev_type="temp_hall" dev_period="month"
                               id="month_hall"><label for="month_hall">мес.</label>
                    </div>
                </div>

                <div class="clear"></div>

                <div class="grid_5 alpha ui-corner-all ui-state-default"
                     style="margin-top:5px;height:215px;width:418px">
                    <h2 style="margin-left:5px;float:left">Температура спальня</h2>
                    <div id="temp_bedroom_g" style="margin-right:5px;float:right"></div>
                    <div style="text-align: center">
                        <?php
                        echo '<img id="g_temp_bedroom" src="graph.php?label=temp_bedroom&t=line&date_from=day&' . rand() . '" height="160">';
                        ?>
                    </div>
                    <div class="rg_g_temp" style="margin-left:5px;float:left">
                        <input type="radio" name="period3" class="set_period" dev_type="temp_bedroom" dev_period="day"
                               checked id="day_temp_bedroom"><label for="day_temp_bedroom">сут.</label>
                        <input type="radio" name="period3" class="set_period" dev_type="temp_bedroom" dev_period="week"
                               id="week_temp_bedroom"><label for="week_temp_bedroom">нед.</label>
                        <input type="radio" name="period3" class="set_period" dev_type="temp_bedroom" dev_period="month"
                               id="month_temp_bedroom"><label for="month_temp_bedroom">мес.</label>
                    </div>
                </div>

                <div class="grid_5 omega ui-corner-all ui-state-default"
                     style="margin-top:5px;height:215px;width:418px">
                    <h2 style="margin-left:5px;float:left">Спальня Леры</h2>
                    <div id="temp_bedroom_Lera_g" style="margin-right:5px;float:right"></div>
                    <div style="text-align: center">
                        <?php
                        echo '<img id="g_temp_bedroom_Lera" src="graph.php?label=temp_bedroom_Lera&t=line&date_from=day&' . rand() . '" height="160">';
                        ?>
                    </div>
                    <div class="rg_g_temp" style="margin-left:5px;float:left">
                        <input type="radio" name="period2" class="set_period" dev_type="temp_bedroom_Lera" dev_period="day"
                               checked id="day_hall"><label for="day_hall">сут.</label>
                        <input type="radio" name="period2" class="set_period" dev_type="temp_bedroom_Lera" dev_period="week"
                               id="week_hall"><label for="week_hall">нед.</label>
                        <input type="radio" name="period2" class="set_period" dev_type="temp_bedroom_Lera" dev_period="month"
                               id="month_hall"><label for="month_hall">мес.</label>
                    </div>
                </div>

                <div class="clear"></div>

            </div>

        </div>
    </div>

    <?php
}

if ($p == 'cam') {

    ?>

    <script type="text/javascript">

        $(document).everyTime("5s", function () {

        });

        $(document).ready(function () {

            $(".button").button();

        });

    </script>

    <div id="page_power" class="grid_11">
        <div class="grid_11 alpha">
            <div class="ui-corner-all ui-widget-header" style="margin-top: 5px">
                <h2 style="margin-left:5px;font-size:150%;">Видеонаблюдение</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_5 alpha ui-corner-all ui-state-default ui-widget-content"
             style="margin-top:5px;height:270px;position:relative;width:418px">
            <div style="text-align: center">
                <img src="http://192.168.1.4:8081/" alt="http://192.168.1.4:8081/" style="margin-top:5px;height:226px;width:400px">
            </div>
            <a class="button" href="cam_archive.php?cam=1" target="_blank" style="margin-left:5px;margin-top:5px">Архив</a>
        </div>
        <div class="grid_5 omega ui-corner-all ui-state-default ui-widget-content"
             style="margin-top:5px;height:270px;position:relative;width:418px">
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
