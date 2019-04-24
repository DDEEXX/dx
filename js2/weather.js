$(document).ready( function() {

    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });

    /* dev=temp - событие = температура */
    /* label=temp_out_1 - имя датчика в базе = temp_out_1*/
    /* type=last - тип события = последнее показание */
    $.get("getData.php?dev=temp&label=temp_out_1&type=last", function (data) {
        $("#temp_out_weather").html(data);
    });


});

$(document).everyTime("3600s", function() {
    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });
});

