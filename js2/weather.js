
function updateWeather() {

    /* dev=temp - событие = температура */
    /* label=temp_out_1 - имя датчика в базе = temp_out_1*/
    /* type=last - тип события = последнее показание */
    $.get("getData.php?dev=temp&label=temp_out_1&type=last", function (data) {
        $("#temp_out_weather").html(data);
    });

}

function updatePressure() {

    /* dev=pressure - событие = давление */
    /* label=pressure - имя датчика в базе = pressure*/
    $.get("getData.php?dev=pressure&label=pressure", function (data) {
        $("#pressure_weather").html(data);
    });

}

$(document).ready( function() {

    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });

    updateWeather();
    updatePressure();

});

$(document).everyTime("3600s", function() {
    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });
});

//Обновление показания погоды каждые 5 минут
$(document).everyTime("300s", function() {
    updateWeather();
    updatePressure();
});
