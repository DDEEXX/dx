$(document).ready(function () {

    $("#accordion_light").accordion();
    $("#backlight_first_floor .button").button( {showLabel: false} );


    $('.lampkey').click(function () {
        var lamp = $(this);
        var label = lamp.attr("label");
        $.get("powerKey.php?label=" + label, function () {
        });
    });

    $.get("getData.php?dev=light&label=light_hol_2_n&type=last&place=250;635&img=backlight", function (data) {
        $("#light_lamp2").html(data);
    });

    $.get("getData.php?dev=light&label=light_stairs_3&type=last&place=220;685&img=backlight", function (data) {
        $("#light_lamp3").html(data);
    });

    $.get("getData.php?dev=light&label=bathroom_mirror_light&type=last&place=295;735&img=backlight", function (data) {
        $("#light_lamp10").html(data);
    });

    $('#backlight_first_floor .button').click(function () {
        var lamp = $(this);
        var code = lamp.attr("mqtt");
        $.get("powerKey.php?label=backlight_first_floor&code=" + code, function () {
        });
    });

});

$(document).everyTime("1s", function () {

    // $.get("getData.php?dev=light&label=light_hol_2&type=last&is_light=is_light_hol_2&place=220;685", function(data)	{
    // 	$("#light_lamp1").html(data);
    // });

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
