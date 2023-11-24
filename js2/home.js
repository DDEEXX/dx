Date.prototype.getMonthName = function () {
    const month = ['января', 'февраля', 'марта', 'апреля', 'майя', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    return month[this.getMonth()];
}

Date.prototype.getDayName = function () {
    const day = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
    return day[this.getDay()];
}

function date() {
    const Data = new Date();
    return Data.getDayName() + ' ' + Data.getDate() + ' ' + Data.getMonthName();
}

function clock() {
    const d = new Date();
    let h = d.getHours();
    let m = d.getMinutes();

    if (h <= 9) h = "0" + h;
    if (m <= 9) m = "0" + m;

    return h + ":" + m;
}

function home_loadSensors() {

    // класс для стиля показаний датчиков
    const classData = 'home_sensor_data';

    $('.sensor_block').each(function () {
        const url = 'data/home/' + $(this).attr('id') + '.json';
        _getSensorProperties(url, classData);
    });

}

function home_updateSensorsOutData() {
    _updateSensorsData('sensor_block', 'data/home/');
}

function home_updateTestStatusDevices() {
    $.post("modules/getData.php", {dev: "test_status"}, function (data) {
        if (data.green) {
            $("#home_status_test_green").addClass('this_status');
        } else {
            $("#home_status_test_green").removeClass('this_status');
        }
        if (data.yellow) {
            $("#home_status_test_yellow").addClass('this_status');
        } else {
            $("#home_status_test_yellow").removeClass('this_status');
        }
        if (data.red) {
            $("#home_status_test_red").addClass('this_status');
        } else {
            $("#home_status_test_red").removeClass('this_status');
        }
    }, "json");

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

    $("#home_cameraFullSize").html('<img src="https://192.168.1.4/camera_0" alt="https://192.168.1.4/camera_0">');

    home_loadSensors();
    home_updateTestStatusDevices();

});

$(document).everyTime("1s", function () {
    $(".TekDate").html(date());
    $(".TekTime").html(clock());
});

//Обновление показания датчиков
$(document).everyTime("60s", function () {
    home_updateSensorsOutData();
    home_updateTestStatusDevices();
});

$(function () {

    const home_cam_dialog = "#home_cameraFullSize";
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

    $("#button_full_screen").button().on("click", function () {
        let elem = document.querySelector("#dx_home");

        if (!document.fullscreenElement) {
            elem.requestFullscreen().catch((err) => {
                alert(
                    `Error attempting to enable fullscreen mode: ${err.message} (${err.name})`,
                );
            });
        } else {
            document.exitFullscreen();
        }
    });

})