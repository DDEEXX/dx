$(function () {

    $("#deltaETemp, #deltaDTemp, #deltaDHum, #deltaEHum").spinner();

    $(".btn_kitchen_hood_set").button();

    $("#power_kitchen_hood_update_info").button().click(function () {
        $.get("updateKitchenHoodInfo.php", function () {});
    });

});
