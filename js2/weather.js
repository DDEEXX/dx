
function updateWeatherData() {

    /* dev=temp - событие = температура */
    /* label=temp_out_1 - имя датчика в базе = temp_out_1*/
    /* type=last - тип события = последнее показание */
    $.get("getData.php?dev=temp&label=temp_out_1", function (data) {
        $("#weather_temperature_out_data").html(data);
    });

    /* dev=pressure - событие = давление */
    /* label=pressure - имя датчика в базе = pressure*/
    $.get("getData.php?dev=pressure&label=pressure", function (data) {
        $("#weather_pressure_data").html(data);
    });

    /* dev=humidity - событие = влажность */
    /* label=humidity_out - имя датчика в базе !!! пока влажность из ванной*/
    $.get("getData.php?dev=humidity&label=bathroom_humidity", function (data) {
        $("#weather_humidity_out_data").html(data);
    });

    /* dev=wind - событие = ветер */
    /* label=wind - имя датчика в базе !!! пока нет*/
    $.get("getData.php?dev=wind&label=wind", function (data) {
        $("#weather_wind_data").html(data);
    });

}

function clickOnWeatherWidget() {
    loadWeatherOutdoorData();
}

function getSensorWidget(data) {
    var newElement = $(data);
    $('#block_weather_outdoor').off('click');
    $('#block_weather_outdoor_data').replaceWith(newElement);
    $('#block_weather_outdoor').on('click', '#block_weather_outdoor_data_widger', clickOnWeatherWidget);
}

function clickOnWeatherSensor() {
    /*параметры виджетов в виде JSON хранятся в файле, имя файла совпадает с id блока на который кликнули*/
    var idBlock = $(this).attr('id');
    var url = 'widget/'+idBlock+'.json';
    $.getJSON(url, function (data) {
        $.get("graphWidget.php", data, getSensorWidget);
    })
}

function loadWeatherOutdoorData() {

    $('#block_weather_outdoor').off('click');
    $('#block_weather_outdoor').load('load2/weather_outdoor.html', function () {
        $('#block_weather_outdoor').on('click', '.expand', clickOnWeatherSensor);
        updateWeatherData();
    });
}

$(document).ready( function() {
    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });

    loadWeatherOutdoorData();
})

//Обновление прогноза погоды 1 раз в час
$(document).everyTime("3600s", function() {
    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });
});

//Обновление показания погоды каждые 10 минут
$(document).everyTime("600s", function() {
    updateWeatherData();
});
