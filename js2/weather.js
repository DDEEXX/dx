function weather_getAllDigitalData() {
    var classData = "weather_sensor_data";
    $("#weather_content").find('.sensor_block').each(function () {
        var id = $(this).attr('id');
        var url = 'data/weather/' + id + '.json';
        _getSensorProperties(url, classData);
    });
}

function weather_updateTemperaturePlan() {
    $.get("getData.php?dev=temp&label=temp_hall&color=plan", function (data) {
        $("#weather_plan_temp_hall_data").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_stair&color=plan", function (data) {
        $("#weather_plan_temp_server_data").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_bedroom&color=plan", function (data) {
        $("#weather_plan_temp_bedroom_data").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_bedroom_Lera&color=plan", function (data) {
        $("#weather_plan_temp_bedroomLera_data").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_bathroom&color=plan", function (data) {
        $("#weather_plan_temp_bathroom_data").html(data);
    });
    $.get("getData.php?dev=humidity&label=bathroom_humidity&color=plan", function (data) {
        $("#weather_plan_humidity_bathroom_data").html(data);
    });
}

function weather_checkPlan() {
    return $("#weather_plan").length;
}

function weather_checkDigitalData() {
    return $("#weather_data").length;
}

function weather_loadDigitalData() {
    $("#weather_content").load('data/weather/weather_digital.html', function () {
        weather_getAllDigitalData();
    });
}

function weather_loadPlan() {
    $("#weather_content").load('data/weather/weather_plan.html', function (){

        $('.sensor_block_plan').each(function () {
            const url = 'data/weather/' + $(this).attr('id') + '.json';
            _getSensorProperties(url);
        });

        //weather_updateTemperaturePlan();
    });
}

function weather_loadSensorsOut() {
    // класс для стиля показаний датчиков
    const classData = 'weather_outdoor_sensor_data';

    $('.sensor_block').each(function () {
        const url = 'data/weather/' + $(this).attr('id') + '.json';
        _getSensorProperties(url, classData);
    });
}

$(document).ready(function () {
    $.get("weather.php", function (data) {
        $("#weather_forecast").html(data)
    });
    $("#weather_button_123").click(function () {
        if (!weather_checkDigitalData()) {
            weather_loadDigitalData();
        }
    })
    $("#weather_button_plan").click(function () {
        if (!weather_checkPlan()) {
            weather_loadPlan();
        }
    })
    weather_loadSensorsOut();
    weather_loadPlan();
})

//Обновление прогноза погоды 1 раз в час
$(document).everyTime("3600s", function () {
    $.get("weather.php", function (data) {
        $("#weather_forecast").html(data)
    });
});

//Обновление показания погоды каждые 10 минут
$(document).everyTime("600s", function () {
    //weather_updateDataAll();
});

$(function () {
    $(".weather_button_setup").button();
})