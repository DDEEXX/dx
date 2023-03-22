
function power_updateAll() {

    //вытяжка
    $.get("getData.php?dev=kitchenHood", function (data) {
        $("#power_kitchen_hood").html(data);
    });

}

function power_checkVent_Status() {

    const curDateStatus = $('#kitchen_hood_last_status').val();

    $.post("getData.php", {dev: "check_ventStatus", dateStatus: curDateStatus}, function (jsonData) {
        if (jsonData['update']) {
            $.get("getData.php?dev=kitchenHood", function (data) {
                $("#power_kitchen_hood").html(data);
            });
        }
    }, "json");

}

$(document).ready(function () {


    // $(".rg_g_vault").buttonset();

    /* dev=label - событие = считать показания цифрового датчика */
    /* label=label_garage_door - имя датчика в базе = label_garage_door*/
    /* type=last - тип события = последнее показание */
    // $.get("getData.php?dev=label&label=label_garage_door&type=last", function (data) {
    //     $("#label_garage_door").html(data);
    // });

    // $.get("getData.php?dev=kitchenHood", function (data) {
    //     $("#power_kitchen_hood").html(data);
    // });



});

$(function () {
    power_updateAll();
});

$(document).everyTime("5s", function () {

    power_checkVent_Status();

    // $.get("getData.php?dev=label&label=label_garage_door&type=last", function (data) {
    //     $("#label_garage_door").html(data);
    // });
});
