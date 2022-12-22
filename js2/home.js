Date.prototype.getMonthName = function () {
    var month = ['января', 'февраля', 'марта', 'апреля', 'майя', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    return month[this.getMonth()];
}

Date.prototype.getDayName = function () {
    var day = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
    return day[this.getDay()];
}

function date() {
    var Data = new Date();
    return Data.getDayName() + ' ' + Data.getDate() + ' ' + Data.getMonthName();
}

function clock() {
    var d = new Date();
    var h = d.getHours();
    var m = d.getMinutes();

    if (h <= 9) h = "0" + h;
    if (m <= 9) m = "0" + m;

    return h + ":" + m;
}

/**
 *  NEW
 * */

function home_loadSensors() {

    // класс для стиля показаний датчиков
    var classData =  'home_sensor_data';

    $('.sensor_block').each(function () {
        var id = $(this).attr('id');
        var url = 'data/home/' + id + '.json';
        _getSensorProperties(url, classData);
    });

}

$(document).ready(function () {

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

    $("#home_cameraFullSize").html('<img src="http://192.168.1.4:8081/" alt="http://192.168.1.4:8081/">');

    home_loadSensors();

});

$(document).everyTime("1s", function () {
    $(".TekDate").html(date());
    $(".TekTime").html(clock());
});

//Обновление показания температуры каждые 5 минут
$(document).everyTime("300s", function () {
//    home_loadOutdoorData();
});

$(function () {

    var home_cam_dialog = "#home_cameraFullSize";
    $(home_cam_dialog).dialog({
        autoOpen: false,
        draggable: false,
        position: {my: "center", at: "center", of: "#page_home"},
        resizable: false,
        title: "Камера",
        height: "auto",
        width: 962
    });

    $(home_cam_dialog).on("click", function () {
        $("#home_cameraFullSize").dialog("close");
    });

    $("#home_cam").on("click", function () {
        $("#home_cameraFullSize").dialog("open");
    });

})