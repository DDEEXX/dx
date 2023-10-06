$(function () {

    $.each(gasSensorsData,function(index, val) {
        let label = val['label'];
        let btn_update_info = label+"_update_info";

        $("#"+btn_update_info).button().click(function () {
            $.get("data/power/gasSensorGet.php?dev=updateInfo&label="+label, function () {});
        });
    })

    $(".property_spinner").spinner();

    $(".btn_gas_sensor_set").button().click( function () {
        const property = $(this).attr("property");
        const label = $(this).attr("label");
        const id_value = "#" + $(this).val();
        const value = $(id_value).val();
        $.post("data/power/gasSensorGet.php?dev=set", {property: property, label: label, value: value});
    });

});
