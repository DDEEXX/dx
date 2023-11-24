function weather_checkPlan() {
    return $("#weather_plan").length;
}

function weather_checkDigitalData() {
    return $("#weather_data").length;
}

function weather_updateSensorsData() {

    _updateSensorsData('sensor_block_out', 'data/weather/');

    if (weather_checkPlan()) {
        _updateSensorsData('sensor_block_plan', 'data/weather/');
    }
    if (weather_checkDigitalData()) {
        _updateSensorsData('sensor_block', 'data/weather/');
    }

}

function weather_getAllDigitalData() {
    const classData = "weather_sensor_data";
    $('.sensor_block').each(function () {
        const url = 'data/weather/' + $(this).attr('id') + '.json';
        _getSensorProperties(url, classData);
    });
}

function weather_getAllPlanData() {
    $('.sensor_block_plan').each(function () {
        const url = 'data/weather/' + $(this).attr('id') + '.json';
        _getSensorProperties(url);
    });
}

function weather_loadDigitalData() {
    $("#weather_content").load('data/weather/weather_digital.html', function () {
        weather_getAllDigitalData();
    });
}

function weather_loadPlan() {
    $("#weather_content").load('data/weather/weather_plan.html', function (){
        weather_getAllPlanData();
    });
}

function weather_loadSensorsOut() {
    // класс для стиля показаний датчиков
    const classData = 'weather_outdoor_sensor_data';

    $('.sensor_block_out').each(function () {
        const url = 'data/weather/' + $(this).attr('id') + '.json';
        _getSensorProperties(url, classData);
    });
}

$(document).ready(function () {
    $.get("modules/weatherGismeteo.php", function (data) {
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
    $.get("modules/weatherGismeteo.php", function (data) {
        $("#weather_forecast").html(data)
    });
});

//Обновление показания датчиков
$(document).everyTime("60s", function () {
    weather_updateSensorsData();
});

$(function () {
    $(".weather_button_setup").button();
})