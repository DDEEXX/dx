$(function () {

    $("#power_kitchen_hood_update_info").button().click(function () {
        $.get("updateKitchenHoodInfo.php", function () {});
    });

});
