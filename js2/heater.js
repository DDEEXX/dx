function updateTemperature() {

    /* dev=temp - событие = температура */
    /* label=temp_out_1 - имя датчика в базе = temp_out_1*/
    /* type=last - тип события = последнее показание */


    $.get("getData.php?dev=temp&label=temp_out_1&type=last", function (data) {
        $("#temp_out_1, #temp_out_1_g").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_hall&type=last&color=plan", function (data) {
        $("#temp_hall").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_bedroom&type=last&color=plan", function (data) {
        $("#temp_bedroom").html(data);
    });

    $.get("getData.php?dev=temp&label=temp_hall&type=last", function (data) {
        $("#temp_hall_g").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_bedroom&type=last", function (data) {
        $("#temp_bedroom_g").html(data);
    });

}

$(document).ready(function () {

    // $('#tempgraph').click(function () {
    //     $.get("dxMainPage.php?p=power1", function () {
    //     });
    // });

    updateTemperature();

    $("#accordion").accordion();

    $(".rg_g_temp").buttonset();

    $(".set_period").click(function () {
        $("#g_" + $(this).attr("dev_type")).attr("src", "graph.php?label=" + $(this).attr("dev_type") +
            "&date_from=" + $(this).attr("dev_period") + "&rnd=" + Math.random());
    });

});

//Обновление показания температуры кажные 5 минут
$(document).everyTime("300s", function () {
    updateTemperature();
});

// $(document).everyTime("120s", function() {
// 	$('#g_temp_out_1').attr('src', 'graph.php?label=temp_out_1&t=line&date_from=day&'+Math.random());
// });

