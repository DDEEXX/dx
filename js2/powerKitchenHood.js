$(function () {

    $("#kh_deltaETemp, #kh_deltaDTemp, #kh_deltaDHum, #kh_deltaEHum").spinner();

    $(".btn_kitchen_hood_set").button().click( function () {
        const property = $(this).attr("property");
        const id_value = "#" + $(this).val();
        const value = $(id_value).val();
        $.post("data/power/setKitchenHoodProperties.php", {property: property, value: value});
    });

    $("#power_kitchen_hood_update_info").button().click(function () {
        $.get("data/power/updateKitchenHoodInfo.php", function () {});
    });

});
