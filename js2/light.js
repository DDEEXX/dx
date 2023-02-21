function light_tile_setEvent() {

    $(".light_tile_click_on_off").on("click", ".light_tile_lamp_click", function () {
        const lamp = $(this);
        const label = lamp.attr("label");
        let value = lamp.attr("value");
        value = value == "on" ? "0" : "1";
        $.get("powerKey.php?label="+label+"&value="+value+"&status=web", function () {});
    });

}

function light_tile_updateAll() {

    $.get("getData.php?dev=light_tile&label=backlight_cabinet_table", function (data) {
        $("#light_tile_cabinet_table").html(data);
    })
    $.get("getData.php?dev=light_tile&label=light_hol_2_n", function (data) {
        $("#light_tile_floor_2_backlight").html(data);
    })
    $.get("getData.php?dev=light_tile&label=light_under_stair", function (data) {
        $("#light_tile_under_stair").html(data);
    })
    $.get("getData.php?dev=light_tile&label=light_stair", function (data) {
        $("#light_tile_stair").html(data);
    })
    $.get("getData.php?dev=light_tile&label=backlight_bathroom", function (data) {
        $("#light_tile_backlight_bathroom").html(data);
    })
    $.get("getData.php?dev=light_tile&label=backlight_understair", function (data) {
        $("#light_tile_backlight_understair").html(data);
    })
    $.get("getData.php?dev=light_tile&label=backlight_kitchen", function (data) {
        $("#light_tile_backlight_kitchen").html(data);
    })
    $.get("getData.php?dev=light_tile&label=light_kitchen_vent", function (data) {
        $("#light_tile_light_kitchen_vent").html(data);
    })




}

$(document).ready(function () {

    light_tile_updateAll();

    $("#backlight_hall .button").button( {showLabel: false} );
    $('#backlight_hall .button').click(function () {
        const value = $(this).attr("value");
        $.get("powerKey.php?label=backlight_first_floor&value=" + value, function () {});
    });
    $( "#backlight_hall_slider" ).slider({
        min: 0,
        max: 8,
        slide: function( event, ui ) {
            let value = ui.value;
            if (value == 0) {value = 8} //min
            else if (value == 8) {value = 9}; //max
            $.get("powerKey.php?label=backlight_first_floor&value=" + value, function () {});

        }
    });

});

$(document).everyTime("1s", function () {

    light_tile_updateAll();

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
    light_tile_setEvent();
})