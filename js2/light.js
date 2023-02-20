function ligth_tile_updateAll() {

    $.get("getData.php?dev=light_tile&label=backlight_cabinet_table", function (data) {
        $("#light_tile_cabinet_table").html(data);
    })

}

$(document).ready(function () {

    $("#backlight_first_floor .button").button( {showLabel: false} );

    ligth_tile_updateAll();

    $('.lampkey').click(function () {
        const lamp = $(this);
        const label = lamp.attr("label");
        $.get("powerKey.php?label=" + label, function () {
        });
    });

    $('#backlight_first_floor .button').click(function () {
        const value = $(this).attr("value_mqtt");
        $.get("powerKey.php?label=backlight_first_floor&value=" + value, function () {
        });
    });

});

$(document).everyTime("1s", function () {

    // $.get("getData.php?dev=light&label=light_hol_2&type=last&is_light=is_light_hol_2&place=220;685", function(data)	{
    // 	$("#light_lamp1").html(data);
    // });

    ligth_tile_updateAll();

    $.get("getData.php?dev=light&label=light_hol_2_n&type=last&place=250;635&img=backlight", function (data) {
        $("#light_lamp2").html(data);
    });

    $.get("getData.php?dev=light&label=light_stairs_3&type=last&place=220;685&img=backlight", function (data) {
        $("#light_lamp3").html(data);
    });

    $.get("getData.php?dev=light&label=bathroom_mirror_light&type=last&place=295;735&img=backlight", function (data) {
        $("#light_lamp10").html(data);
    });

});

$(function () {
    $(".light_button_setup").button();
})