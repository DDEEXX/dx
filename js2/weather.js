$(document).ready( function() {

    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });

});

$(document).everyTime("3600s", function() {
    $.get("weather.php", function(data) {
        $("#weather_forecast").html(data)
    });
});

