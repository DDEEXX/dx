$(document).ready(function () {

    $(".rg_g_vault").buttonset();

    /* dev=label - событие = считать показания цифрового датчика */
    /* label=label_garage_door - имя датчика в базе = label_garage_door*/
    /* type=last - тип события = последнее показание */
    $.get("getData.php?dev=label&label=label_garage_door&type=last", function (data) {
        $("#label_garage_door").html(data);
    });
});

$(document).everyTime("5s", function () {
    $.get("getData.php?dev=label&label=label_garage_door&type=last", function (data) {
        $("#label_garage_door").html(data);
    });
});
