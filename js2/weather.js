
function updateWeather() {

    /* dev=temp - событие = температура */
    /* label=temp_out_1 - имя датчика в базе = temp_out_1*/
    /* type=last - тип события = последнее показание */
    $.get("getData.php?dev=temp&label=temp_out_1", function (data) {
        $("#weather_temperature_out").html(data);
    });

    /* dev=pressure - событие = давление */
    /* label=pressure - имя датчика в базе = pressure*/
    $.get("getData.php?dev=pressure&label=pressure", function (data) {
        $("#weather_pressure").html(data);
    });

    /* dev=humidity - событие = влажность */
    /* label=humidity_out - имя датчика в базе !!! пока влажность из ванной*/
    $.get("getData.php?dev=humidity&label=bathroom_humidity", function (data) {
        $("#weather_humidity_out").html(data);
    });

    /* dev=wind - событие = ветер */
    /* label=wind - имя датчика в базе !!! пока нет*/
    $.get("getData.php?dev=wind&label=wind", function (data) {
        $("#weather_wind").html(data);
    });

}

$(document).ready( function() {

    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });

    updateWeather();

});

//Обновление прогноза погоды 1 раз в час
$(document).everyTime("3600s", function() {
    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });
});

//Обновление показания погоды каждые 5 минут
$(document).everyTime("300s", function() {
    updateWeather();
});
