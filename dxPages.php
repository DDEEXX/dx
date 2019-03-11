<?php
$p = isset($_REQUEST['p'])?$_REQUEST['p']:"home";

$alarm = 'off'; //Это затычка

if ($alarm!='off') {
	$p="alarm";
}

if ($p=="alarm") {
?>

<script>
  $( function() {
		
		var $alarm_p = $('#alarm_p'),
				$alarm_pass = $('#alarm_pass');				   
		
		$alarm_pass.val("");		
		
    $( ".alarm_button" ).button();
    $( ".alarm_button" ).click( function(event) {
			
			var $this = $(this),
			character = $this.attr("letter");
			
			if ($this.hasClass('alarm_button_delete')) {
				var html = $alarm_p.html(),
						val = $alarm_pass.val();
 
				$alarm_p.html(html.substr(0, html.length - 2));
				$alarm_pass.val(val.substr(0, val.length - 1));
				return false;
			}			

			if ($this.hasClass('alarm_button_ok')) {
				var val = $alarm_pass.val();
				$.get("alarm.php?p=off&pp="+val, function(data){});
				location.reload(true);
				return false;
			}			
			
			if ($alarm_pass.val().length<5) {
				$alarm_p.html($alarm_p.html() + '* ');
				$alarm_pass.val($alarm_pass.val() + character);
			}
			
    } );   
    
  } );
</script>

<div id="page_alarm", class="grid_11">
	<div class = "grid_11 alpha ui-corner-all ui-widget-header" style="margin-top: 5px">
		<h2 style="margin-left:5px;font-size:150%;">Введите код снятия с сигнализации</h2>
	</div>
	<div class = "clear"></div>
	<div class = "grid_11 alpha">
		<div class = "grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:90px;border:0px">
		</div>
		<div class = "grid_9 ui-corner-all ui-state-default" style="margin-top:5px;height:90px;width:698px">
			<h2 id="alarm_p" style="font-size:80px;text-align:center"></h2>
			<input id="alarm_pass" type="hidden"></input>
		</div>
		<div class = "grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:90px;border:0px">
		</div>
		<div class = "clear"></div>
		
		<div class = "grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0px">
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="1">
			<h2 style="font-size:110px">1</h2>
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="2">
			<h2 style="font-size:110px">2</h2>
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="3">
			<h2 style="font-size:110px">3</h2>
		</div>
		<div class = "grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0px">
		</div>
		<div class = "clear"></div>
		
		<div class = "grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0px">
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="4">
			<h2 style="font-size:110px">4</h2>
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="5">
			<h2 style="font-size:110px">5</h2>
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="6">
			<h2 style="font-size:110px">6</h2>
		</div>
		<div class = "grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0px">
		</div>
		<div class = "clear"></div>

		<div class = "grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0px">
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="7">
			<h2 style="font-size:110px">7</h2>
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="8">
			<h2 style="font-size:110px">8</h2>
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="9">
			<h2 style="font-size:110px">9</h2>
		</div>
		<div class = "grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0px">
		</div>
		<div class = "clear"></div>

		<div class = "grid_1 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0px">
		</div>
		<div class = "grid_3 alarm_button alarm_button_delete ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px">
			<h2 style="font-size:110px;color:yellow"><</h2>
		</div>
		<div class = "grid_3 alarm_button ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px" letter="0">
			<h2 style="font-size:110px">0</h2>
		</div>
		<div class = "grid_3 alarm_button alarm_button_ok ui-corner-all ui-state-default" style="margin-top:5px;height:130px;width:218px">
			<h2 style="font-size:110px;color:green">OK</h2>
		</div>
		<div class = "grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:130px;border:0px">
		</div>
		<div class = "clear"></div>
		
		
	</div>
</div>

<?php
}

if ( $p == "home" || empty($p) ) {
?>

<script src="js2/home.js"></script>

<div id="page_home", class="grid_11">
	<!--
	<div class = "grid_11 alpha ui-corner-all ui-widget-header" style="margin-top: 5px">
		<h2 style="margin-left:5px;font-size:150%;">Общая информация</h2>
	</div>
	<div class = "clear"></div>
	-->
	<div class = "grid_9 alpha homeweather">
	</div>
	<div class = "grid_2 omega">
		<div id="TekDate" class="TekDate" style="font-size:120%"></div><br>
		<div id="TekTime" class="TekTime" style="font-size:160%"></div>						
	</div>
	
	<div class = "clear"></div>
	
	<div id="alarm_key" class = "grid_2 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:100px;width:138px">
		<h2 style="margin-left:5px">Охрана</h2>
		<div style="text-align:center;margin-top:5px">
			<img src="img2/icon/lock a.png">
		</div>
	</div>
	
</div>

<?php
}
if ( $p == "temp" ) {
	
?>

<script type="text/javascript">

	$(document).ready( function() {
		
		$('#tempgraph').click(function () {
				$.get("dxMainPage.php?p=power1", function(data){});
		});
		
		/* dev=temp - событие = температура */
		/* label=temp_out_1 - имя датчика в базе = temp_out_1*/
		/* type=last - тип события = последнее показание */
		$.get("getData.php?dev=temp&label=temp_out_1&type=last", function(data)	{
			$("#temp_out_1").html(data);
		});
		$.get("getData.php?dev=temp&label=temp_hall&type=last", function(data) {
			$("#temp_hall").html(data);
		});
        $.get("getData.php?dev=temp&label=temp_bedroom&type=last", function(data) {
            $("#temp_bedroom").html(data);
        });

		$("#accordion").accordion();
		
		$(".rg_g_temp").buttonset();
		
		$(".set_period").click(function () {
			$("#g_" + $(this).attr("dev_type")).attr("src", "graph.php?label="+$(this).attr("dev_type")+"&date_from="+$(this).attr("dev_period")+"&rnd="+Math.random());
		});
		
	});

	// $(document).everyTime("300s", function(i) {
	// 	$.get("getData.php?dev=temp&label=temp_out_1&type=last", function(data) {
	// 		$("#temp_out_1").html(data);
	// 	});
     //    $.get("getData.php?dev=temp&label=temp_hall&type=last", function(data) {
     //        $("#temp_hall").html(data);
     //    });
	// });
	
	// $(document).everyTime("60s", function(i) {
	// 	$('#g_temp_out_1').attr('src', 'graph.php?label=temp_out_1&t=line&date_from=day&'+Math.random());
	// });
	

</script>

<div id="page_temp", class="grid_11">
	<div class = "grid_11 alpha">
		<div class = "ui-corner-all ui-widget-header" style="margin-top: 5px">
			<h2 style="margin-left:5px;font-size:150%;">Температура</h2>
		</div>
	</div>
	<div class = "clear"></div>
	
	<div id="accordion">
		
		<h3 class='dx'>План</h3>
		<div style="padding:0;border:0;overflow:visible">
			<div class = "grid_3 alpha">
				<div class = "ui-corner-all ui-state-default" style="margin-top:5px;height:80px">
					<h2 style="margin-left:5px">Температура на улице</h2>
					<img style="margin-top:5px;float:left" src="img2/temp.png">
					<div id="temp_out_1" class="temp_out_1" style="margin-top:10px;"></div>
				</div>
			</div>
			<div class = "grid_3 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:218px">
			</div>
			<div class = "grid_3 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:218px">
			</div>
			<div class = "grid_1 ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:58px">
			</div>
			<div class = "grid_1 omega ui-corner-all ui-state-default" style="margin-top:5px;height:80px;width:58px">
			</div>
			<div class = "clear"></div>
			<div class = "grid_11 alpha">
				<div class = "ui-corner-all ui-state-default ui-widget-content" style="height: 500px;margin-top:5px;position:relative">
				    <div id="floor1"><img src="img2/home_.png"></div>
				    <div id="temp_hall" class="ui-corner-all ui-state-default ui-widget-content" style="position:absolute;left:120px;top:220px"></div>
                    <div id="temp_bedroom" class="ui-corner-all ui-state-default ui-widget-content" style="position:absolute;left:560px;top:310px"></div>
				</div>
			</div>
			<div class = "clear"></div>	
		</div>
		<h3 class='dx'>Графики</h3>
		<div style="padding:0;border:0;overflow:visible">
			<div class = "grid_5 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:215px;width:418px">
				<h2 style="margin-left:5px;float:left">Температура на улице</h2>
				<div class="temp_out_1 g_temp"></div>
				<div style="text-align: center">
					<?php
						echo '<img id="g_temp_out_1" src="graph.php?label=temp_out_1&t=line&date_from=day&'.rand().'" height="160">';
					?>
				</div>
				<div class="rg_g_temp" style="margin-left:5px;float:left">
					<input type="radio" name="period1" class="set_period" dev_type="temp_out_1" dev_period="day" checked id="day_out1"><label for="day_out1">сут.</label>
                    <input type="radio" name="period1" class="set_period" dev_type="temp_out_1" dev_period="week" id="week_out1"><label for="week_out1">нед.</label>
					<input type="radio" name="period1" class="set_period" dev_type="temp_out_1" dev_period="month" id="month_out1"><label for="month_out1">мес.</label>
				</div>
			</div>
			
			<div class = "grid_5 omega ui-corner-all ui-state-default" style="margin-top:5px;height:215px;width:418px">
				<h2 style="margin-left:5px;float:left">Температура 1 этаж</h2>
				<div class="temp_hall g_temp"></div>
				<div style="text-align: center">
					<?php
						echo '<img id="g_temp_hall" src="graph.php?label=temp_hall&t=line&date_from=day&'.rand().'" height="160">';
					?>
				</div>
				<div class="rg_g_temp" style="margin-left:5px;float:left">
					<input type="radio" name="period2" class="set_period" dev_type="temp_hall" dev_period="day" checked id="day_hall"><label for="day_hall">сут.</label>
                    <input type="radio" name="period2" class="set_period" dev_type="temp_hall" dev_period="week" id="week_hall"><label for="week_hall">нед.</label>
					<input type="radio" name="period2" class="set_period" dev_type="temp_hall" dev_period="month" id="month_hall"><label for="month_hall">мес.</label>
				</div>
			</div>

            <div class = "clear"></div>

            <div class = "grid_5 alpha ui-corner-all ui-state-default" style="margin-top:5px;height:215px;width:418px">
                <h2 style="margin-left:5px;float:left">Температура спальня</h2>
                <div class="temp_bedroom g_temp"></div>
                <div style="text-align: center">
                    <?php
                    echo '<img id="g_temp_bedroom" src="graph.php?label=temp_bedroom&t=line&date_from=day&'.rand().'" height="160">';
                    ?>
                </div>
                <div class="rg_g_temp" style="margin-left:5px;float:left">
                    <input type="radio" name="period3" class="set_period" dev_type="temp_bedroom" dev_period="day" checked id="day_temp_bedroom"><label for="day_temp_bedroom">сут.</label>
                    <input type="radio" name="period3" class="set_period" dev_type="temp_bedroom" dev_period="week" id="week_temp_bedroom"><label for="week_temp_bedroom">нед.</label>
                    <input type="radio" name="period3" class="set_period" dev_type="temp_bedroom" dev_period="month" id="month_temp_bedroom"><label for="month_temp_bedroom">мес.</label>
                </div>
            </div>

		</div>
	
	</div>
</div>

<?php
}

//Освещение
if ( $p == "light" ) {
?>

<style>
	.lamp		{position: absolute;height: 40px;width: 40px;border-radius: 50%;}
	.lampkey	{position: absolute;height: 40px;width: 40px;border-radius: 50%;}
	.lamp_img	{position: absolute}
	.on_l		{box-shadow: 0px 0px 60px 60px rgba(255, 191, 0, 0.3);border-radius: 100%;}
	.on			{border: 1px solid #ffbf00}
	.off		{border: 1px solid #86aeff}
</style>

<script type="text/javascript">

	$(document).ready(function() {

		$('.lampkey').click(function () {
				var lamp = $(this);
				var label = lamp.attr("label");
				$.get("key.php?label="+label, function(data){});
		});
		
	});

	$(document).everyTime("1s", function(i) {
		
		$.get("getData.php?dev=light&label=light_hol_2&type=last&is_light=is_light_hol_2&place=220;685", function(data)	{
			$("#light_lamp1").html(data);
		});
	
		$.get("getData.php?dev=light&label=light_hol_2_n&type=last&place=250;635", function(data)	{
			$("#light_lamp2").html(data);
		});

	});


</script> 

<div id="page_light", class="grid_11">
	<div class = "grid_11 alpha">
		<div class = "ui-corner-all ui-widget-header" style="margin-top: 5px">
			<h2 style="margin-left:5px;font-size:150%;">Освещение</h2>
		</div>
	</div>
	<div class = "clear"></div>
	<div class = "grid_3 alpha">
		<div class = "ui-corner-all ui-state-default" style="margin-top:5px;height:80px">
		</div>
	</div>
	<div class = "clear"></div>
	<div class = "grid_11 alpha">
	  <div class = "ui-corner-all ui-state-default ui-widget-content" style="height: 500px;margin-top:5px;position:relative;">
		<div id="home_light"><img src="img2/home_.png">
			<div id="light_lamp1"></div>
			<div class='lampkey' label='light_hol_2' style='top:220px;left:685px'></div>
			<div id="light_lamp2"></div>
			<div class='lampkey' label='light_hol_2_n' style='top:250px;left:635px'></div>
		</div>
	  </div>
	</div>
	<div class = "clear"></div>
</div>

<?php
}

if ( $p == "power") {
?>

<script type="text/javascript">

	$(document).ready(function() {
		/* dev=label - событие = считать показания цифрового датчика */
		/* label=label_garage_door - имя датчика в базе = label_garage_door*/
		/* type=last - тип события = последнее показание */
		$.get("getData.php?dev=label&label=label_garage_door&type=last", function(data)	{
			$("#label_garage_door").html(data);
		});
	});

	$(document).everyTime("5s", function(i) {
		$.get("getData.php?dev=label&label=label_garage_door&type=last", function(data) {
			$("#label_garage_door").html(data);
		});
	});

</script>
<div id="page_power" class="grid_11">
	<div class = "grid_11 alpha">
		<div class = "ui-corner-all ui-widget-header" style="margin-top: 5px">
			<h2 style="margin-left:5px;font-size:150%;">Исполнители</h2>
		</div>
	</div>
	<div class = "clear"></div>
	<div class = "grid_3 alpha">
		<div class = "ui-corner-all ui-state-default ui-widget-content" style="margin-top:5px;height:90px;position:relative">
			<h2 style="margin-left:5px">Гаражные ворота</h2>
			<div id="label_garage_door" style="float:left;margin-left:8px;margin-top:2px"></div>
			<button style="margin-left:20px;margin-top:10px;" class="upDown"></button>
		</div>
	</div>
	<div class = "grid_3 alpha">
		<div class = "ui-corner-all ui-state-default ui-widget-content" style="margin-top:5px;height:90px;position:relative">
			<h2 style="margin-left:5px">Ворота</h2>
		</div>
	</div>
	<div class = "grid_3 alpha">
		<div class = "ui-corner-all ui-state-default ui-widget-content" style="margin-top:5px;height:90px;position:relative">
			<h2 style="margin-left:5px">Калитка</h2>
		</div>
	</div>
</div>
<?php
}

if ( $p == "cam") {

?>

<script type="text/javascript">
	
	$(document).ready( function() {
		
		$(".button").button();		
		
	});
	
</script>

<div id="page_power" class="grid_11">
	<div class = "grid_11 alpha">
		<div class = "ui-corner-all ui-widget-header" style="margin-top: 5px">
			<h2 style="margin-left:5px;font-size:150%;">Видеонаблюдение<h2>
		</div>
	</div>
	<div class = "clear"></div>
	<div class = "grid_5 alpha ui-corner-all ui-state-default ui-widget-content" style="margin-top:5px;height:270px;position:relative;width:418px">
		<div style="text-align: center">
			<img id="monitor1" style="margin-top:5px;height:225px;width:400px" src="/zm/cgi-bin/nph-zms?mode=jpeg&scale=35&maxfps=0.5&buffer=1000&monitor=1&connkey=967224&rand=1483467515">
		</div>
		<a class="button" href="/zm/index.php?view=watch&mid=1" target="_blank" style="margin-left:5px;margin-top:5px">Просмотр в ZM</a>
		<a class="button" href="cam_archive.php?m=1" target="_blank" style="margin-left:5px;margin-top:5px">Архив</a>
	</div>
	<div class = "grid_5 omega ui-corner-all ui-state-default ui-widget-content" style="margin-top:5px;height:270px;position:relative;width:418px">
	</div>
</div>

<?php
}
?>
