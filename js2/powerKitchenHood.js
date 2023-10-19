$(function () {

    $(".property_spinner").spinner();

    $(".btn_kitchen_hood_set").button().click( function () {
        const property = $(this).attr("property");
        const id_value = "#" + $(this).val();
        const value = $(id_value).val();
        $.post("data/power/kitchenHood.php", {dev: "setProperties", property: property, value: value});
    });

    $("#power_kitchen_hood_update_info").button().click(function () {
        $.get("data/power/kitchenHood.php?dev=update", function () {});
    });

});
