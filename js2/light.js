
$(document).ready(function () {

});

$(document).everyTime("1s", function () {

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
