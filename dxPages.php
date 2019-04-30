<?php
$p = isset($_REQUEST['p']) ? $_REQUEST['p'] : "home";

$alarm = 'off'; //Это затычка

if ($alarm != 'off') {
    $p = "alarm";
}

if ($p == "alarm") {
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

if ($p == "home" || empty($p)) {
    ?>

    <script src="js2/home.js"></script>

    <div id="page_home" class="grid_11">
        <div class="grid_9 alpha">1
        </div>
        <div class="grid_2 omega">
            <div id="TekDate" class="TekDate" style="font-size:120%"></div>
            <br>
            <div id="TekTime" class="TekTime" style="font-size:160%"></div>
        </div>

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

if ($p == "weather") {
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
                    <h3 class="Title1">Прогноз погоды</h3>
                    <div class="clear"></div>
                    <div id="weather_forecast"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_5 alpha">
            <p>1</p>
        </div>
        <div class="grid_6 omega">
            <div class="ui-corner-all ui-state-default" style="margin-top:5px;height:180px">
                <h2 style="margin-left:5px">Погода на улице</h2>
                <div class="grid_3 alpha">
                    <div style="overflow: hidden">
                        <img style="margin-top:5px;margin-left:5px" src="img2/thermometer.png">
                        <div id="temp_out_weather" style="margin-top:10px;margin-left:15px;float:right">--</div>
                    </div>
                </div>
                <div class="grid_3 omega" style="margin-left: 5px">
                    <div style="overflow: hidden">
                        <img style="margin-top:5px;margin-left:10px;float:left" src="img2/barometer.png">
                        <div id="pressure_weather" style="float:left;margin-left:15px;margin-top:10px;">--</div>
                        <div style="float:right;margin-right:2px;margin-top:27px;font-size: 80%"> мм рт.ст.</div>
                    </div>
                </div>
                <div class="clear"></div>
                <div class="grid_3 alpha">
                    <div style="overflow: hidden;margin-top: 10px">
                        <img style="margin-top:5px;margin-left:12px;float:left" src="img2/windsock.png">
                        <div id="wind_weather" style="float:left;margin-left:15px;margin-top:10px;">--</div>
                        <div style="float:left;margin-left:2px;margin-top:27px;font-size: 80%">м/с</div>
                        <div id="wind_dir_weather" style="float:left;margin-left:15px;margin-top:10px;">сз</div>
                    </div>
                </div>
                <div class="grid_3 omega" style="margin-left: 5px">
                    <div style="overflow: hidden;margin-top: 10px">
                        <p style="float:right;font-size: 80%">изменение давления</p>
                        <img src="pressureHistory.php" height=80 style="float:right">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
}

//Освещение
if ($p == "light") {
    ?>

    <script type="text/javascript">

        $(document).ready(function () {

            $('.lampkey').click(function () {
                var lamp = $(this);
                var label = lamp.attr("label");
                $.get("powerKey.php?label=" + label, function () {
                });
            });

            $.get("getData.php?dev=light&label=light_hol_2_n&type=last&place=250;635&img=backlight", function (data) {
                $("#light_lamp2").html(data);
            });

        });

        $(document).everyTime("1s", function () {

            // $.get("getData.php?dev=light&label=light_hol_2&type=last&is_light=is_light_hol_2&place=220;685", function(data)	{
            // 	$("#light_lamp1").html(data);
            // });

            $.get("getData.php?dev=light&label=light_hol_2_n&type=last&place=250;635&img=backlight", function (data) {
                $("#light_lamp2").html(data);
            });

        });


    </script>

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
        <div class="grid_11 alpha">
            <div class="ui-corner-all ui-state-default ui-widget-content"
                 style="height: 500px;margin-top:5px;position:relative;">
                <div id="home_light"><img src="img2/home_.png">
                    <div id="light_lamp1"></div>
                    <div class='lampkey' label='light_hol_2' style='top:220px;left:685px'></div>
                    <div id="light_lamp2"></div>
                    <div class='lampkey' label='light_hol_2_n' style='top:250px;left:635px'></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <?php
}

if ($p == "power") {
    ?>

    <script type="text/javascript">

        $(document).ready(function () {
            /* dev=label - событие = считать показания цифрового датчика */
            /* label=label_garage_door - имя датчика в базе = label_garage_door*/
            /* type=last - тип события = последнее показание */
            $.get("getData.php?dev=label&label=label_garage_door&type=last", function (data) {
                $("#label_garage_door").html(data);
            });
        });

        $(document).everyTime("5s", function () {
            $.get("getData.php?dev=label&label=label_garage_door&type=last", function (data) {
                $("#label_garage_door").html(data);
            });
        });

    </script>
    <div id="page_power" class="grid_11">
        <div class="grid_11 alpha">
            <div class="ui-corner-all ui-widget-header" style="margin-top: 5px">
                <h2 style="margin-left:5px;font-size:150%;">Исполнители</h2>
            </div>
        </div>
        <div class="clear"></div>
        <div class="grid_3 alpha">
            <div class="ui-corner-all ui-state-default ui-widget-content"
                 style="margin-top:5px;height:90px;position:relative">
                <h2 style="margin-left:5px">Гаражные ворота</h2>
                <div id="label_garage_door" style="float:left;margin-left:8px;margin-top:2px"></div>
                <button style="margin-left:20px;margin-top:10px;" class="upDown"></button>
            </div>
        </div>
        <div class="grid_3 alpha">
            <div class="ui-corner-all ui-state-default ui-widget-content"
                 style="margin-top:5px;height:90px;position:relative">
                <h2 style="margin-left:5px">Ворота</h2>
            </div>
        </div>
        <div class="grid_3 alpha">
            <div class="ui-corner-all ui-state-default ui-widget-content"
                 style="margin-top:5px;height:90px;position:relative">
                <h2 style="margin-left:5px">Калитка</h2>
            </div>
        </div>
    </div>
    <?php
}

if ($p == "heater") {
    ?>
    <script src="js2/heater.js"></script>

    <div id="page_temp" class="grid_11">
        <div class="grid_11 alpha">
            <div class="ui-corner-all ui-widget-header" style="margin-top: 5px">
                <h2 style="margin-left:5px;font-size:150%;">Отопление и климат</h2>
            </div>
        </div>
        <div class="clear"></div>

        <div id="accordion">

            <h3 class='dx'>План</h3>
            <div style="padding:0;border:0;overflow:visible">
                <div class="grid_3 alpha">
                    <div class="ui-corner-all ui-state-default" style="margin-top:5px;height:80px">
                        <h2 style="margin-left:5px">Температура на улице</h2>
                        <img style="margin-top:5px;float:left" src="img2/temp.png">
                        <div id="temp_out_1" style="margin-top:10px;"></div>
                    </div>
                </div>
                <div class="grid_3 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:218px">
                </div>
                <div class="grid_3 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:218px">
                </div>
                <div class="grid_1 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:58px">
                </div>
                <div class="grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:58px">
                </div>
                <div class="clear"></div>
                <div class="grid_11 alpha">
                    <div class="ui-corner-all ui-state-default ui-widget-content"
                         style="height: 500px;margin-top:5px;position:relative">
                        <div id="floor1"><img src="img2/home_.png"></div>
                        <div id="temp_hall" class="ui-corner-all ui-state-default ui-widget-content"
                             style="position:absolute;left:120px;top:220px"></div>
                        <div id="temp_bedroom" class="ui-corner-all ui-state-default ui-widget-content"
                             style="position:absolute;left:560px;top:310px"></div>
                        <div id="temp_cubie" class="ui-corner-all ui-state-default ui-widget-content"
                             style="position:absolute;left:220px;top:160px"></div>
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

            </div>

        </div>
    </div>

    <?php
}

if ($p == "cam") {

    ?>

    <script type="text/javascript">

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
            <!--		<div style="text-align: center">-->
            <!--			<img id="monitor1" style="margin-top:5px;height:225px;width:400px" src="/zm/cgi-bin/nph-zms?mode=jpeg&scale=35&maxfps=0.5&buffer=1000&monitor=1&connkey=967224&rand=1483467515">-->
            <!--		</div>-->
            <!--		<a class="button" href="/zm/index.php?view=watch&mid=1" target="_blank" style="margin-left:5px;margin-top:5px">Просмотр в ZM</a>-->
            <!--		<a class="button" href="cam_archive.php?m=1" target="_blank" style="margin-left:5px;margin-top:5px">Архив</a>-->
        </div>
        <div class="grid_5 omega ui-corner-all ui-state-default ui-widget-content"
             style="margin-top:5px;height:270px;position:relative;width:418px">
        </div>
    </div>

    <?php
}
?>
