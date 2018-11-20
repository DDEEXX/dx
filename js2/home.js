Date.prototype.getMonthName = function() {
	//var month = ['������','�������','����','������','���','����',
	//'����','������','��������','�������','������','�������'];
	var month = ['������','�������','�����','������','����','����',
	'����','�������','��������','�������','������','�������'];
	return month[this.getMonth()];
}

Date.prototype.getDayName = function() {
	//var day = ['�����������', '�����������', '�������', '�����', '�������', '�������', '�������'];
	var day = ['��', '��', '��', '��', '��', '��', '��'];
	return day[this.getDay()];
}


function date()	{
	Data = new Date();
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
	
	$("#alarm_key").button();		
	$("#alarm_key").click(function () {
		$.get("alarm.php?p=on", function(data){});
		console.info("123");
		location.reload(true);
	});
	
	$(".TekDate").html(date());		
	$(".TekTime").html(clock());		
	$.get("weather.php", function(data) {
			$(".homeweather").html(data)
	});	
	
});

$(document).everyTime("1s", function(i) {
	$(".TekDate").html(date());		
	$(".TekTime").html(clock());		
});

$(document).everyTime("3600s", function(i) {
	$.get("weather.php", function(data) {
			$(".homeweather").html(data)
	});		
});

