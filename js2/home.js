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

function home_clickOnDataSensor() {
	/*параметры виджетов в виде JSON хранятся в файле, имя файла совпадает с id*/
	var idBlock = $(this).parent().parent().parent().attr('id');
	var idBlockClick = $(this).attr('id');
	var url = 'data/home/widget/' + idBlock + '.json';
	var selector = "#"+idBlock;
	$(selector).off('click');
	getWidgetSensor(idBlock, idBlockClick, url);
	$(selector).on('click', function () {
		home_loadOutdoorData(idBlock);
	});
}

function home_loadOutdoorData() {
	var idBlock = "block_home_outdoor";
	var selector = "#"+idBlock;
	$(selector).off('click');
	$(selector).load('data/home/load/block_home_outdoor.html', function (response, status) {
		if (status === 'success') {
			$(selector).on('click', '.expand', home_clickOnDataSensor);
			var url = 'data/home/widget/' + idBlock + '.json';
			getDataSensor(url); //функция для обновления показаний датчиков
		}
	});
}

$(document).ready( function() {

/*
	var $alarmKey = $('#alarm_key');
	$alarmKey.button();
	$alarmKey.click(function () {
		$.get("alarm.php?p=on", function(data){});
		console.info("123");
		location.reload(true);
	});
*/

	$(".TekDate").html(date());		
	$(".TekTime").html(clock());

	home_loadOutdoorData();

});

$(document).everyTime("1s", function() {
	$(".TekDate").html(date());		
	$(".TekTime").html(clock());		
});

//Обновление показания температуры каждые 5 минут
$(document).everyTime("300s", function () {
	home_loadOutdoorData();
});

$(function () {

	$( "#home_cameraFullSize" ).dialog({
		autoOpen: false,
		draggable: false,
		position: { my: "center", at: "center", of: "#page_home" },
		resizable: false,
		title: "Камера",
		height: "auto",
		width: 962,
	});

	$("#home_cam").on( "click", function() {
		$("#home_cameraFullSize").dialog("open");
	});
	$("#home_cameraFullSize").on( "click", function() {
		$("#home_cameraFullSize").dialog("close");
	});

})