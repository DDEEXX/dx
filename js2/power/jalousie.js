
$(function () {

    $(".power_jalousie_hall_1").button().click(function () {
        const label = $(this).attr("label");
        const value = $(this).val();
        $.post("modules/setData.php", {label: label, value: value});
    });
    $(".power_jalousie_hall_control_vertical").controlgroup({
        "direction": "vertical"
    });

});