
function updateWeather() {

    /* dev=temp - событие = температура */
    /* label=temp_out_1 - имя датчика в базе = temp_out_1*/
    /* type=last - тип события = последнее показание */
    $.get("getData.php?dev=temp&label=temp_out_1&type=last", function (data) {
        $("#temp_out_weather").html(data);
    });

    $.get("getData.php?dev=pressure&label=pressure_cube&type=last", function (data) {
        $("#pressure_weather").html(data);
    });

}

$(document).ready( function() {

    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });

    updateWeather();

});

$(document).everyTime("3600s", function() {
    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });
});

//Обновление показания погоды кажные 5 минут
$(document).everyTime("300s", function() {
    updateWeather();
});
