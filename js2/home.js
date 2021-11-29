function updateTemperature() {

    /* dev=temp - событие = температура */
    /* label=temp_out_1 - имя датчика в базе = temp_out_1*/
    /* type=last - тип события = последнее показание */

    $.get("getData.php?dev=temp&label=temp_out_1&type=last", function (data) {
        $("#temp_out_weather_home").html(data);
    });

}

function updatePressure() {
	/* dev=pressure - событие = давление */
	/* label=pressure - имя датчика в базе = pressure*/
	$.get("getData.php?dev=pressure&label=pressure", function (data) {
		$("#pressure_weather_home").html(data);
	});

}

Date.prototype.getMonthName = function() {
	var month = ['января', 'февраля', 'марта', 'апреля', 'майя', 'июня',
		'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
	return month[this.getMonth()];
}

Date.prototype.getDayName = function() {
	var day = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
	return day[this.getDay()];
}


function date()	{
	var Data = new Date();
	return Data.getDayName()+' '+Data.getDate()+' '+Data.getMonthName();
}

function clock() {
	var d = new Date();
	var h = d.getHours();
	var m = d.getMinutes();

	if (h <= 9) h="0" + h;
	if (m <=9 ) m="0" + m;

	return h + ":" + m;
}	

$(document).ready( function() {

    updateTemperature();
	updatePressure();

	var $alarmKey = $('#alarm_key');
	$alarmKey.button();
	$alarmKey.click(function () {
		$.get("alarm.php?p=on", function(data){});
		console.info("123");
		location.reload(true);
	});
	
	$(".TekDate").html(date());		
	$(".TekTime").html(clock());		

});

$(document).everyTime("1s", function() {
	$(".TekDate").html(date());		
	$(".TekTime").html(clock());		
});

//Обновление показания температуры каждые 5 минут
$(document).everyTime("300s", function () {
    updateTemperature();
	updatePressure();
});
