
function power_updateAll() {

    $.get("getData.php?dev=kitchenHood", function (data) {
        $("#power_kitchen_hood").html(data);
    });
    $.get("getData.php?dev=gasSensor&label=gas_sensor_kitchen", function (data) {
        $("#power_kitchen_gas_sensor").html(data);
    });

}

function power_checkVent_Status() {

    const curDateStatus = $('#kitchen_hood_last_status').val();

    $.post("getData.php", {dev: "check_ventStatus", dateStatus: curDateStatus}, function (jsonData) {
        if (jsonData['update']) {
            $.get("getData.php?dev=kitchenHood", function (data) {
                $("#power_kitchen_hood").html(data);
            });
        } else {
            let arLabels = [];
            arLabels.push('light_kitchen_vent');
            $.post("getData.php", {dev: "check_value", 'labels[]': arLabels}, function (jsonData) {
                let res = jsonData.find(i => i.label === 'light_kitchen_vent');
                if (res) {
                    const value = $('#power_kitchen_hood_light').attr("value");
                    if ((value === 'on' && res.value !== 1) ||
                        (value === 'off' && res.value === 1)) {
                        $.get("getData.php?dev=kitchenHood", function (data) {
                            $("#power_kitchen_hood").html(data);
                        });
                    }
                }
            }, "json");
        }
    }, "json");

}

function power_checkKitchenGasSensor_Status() {

    const curDateStatus = $('#kitchen_gas_sensor_last_status').val();

    $.post("getData.php", {dev: "check_gasSensorStatus", dateStatus: curDateStatus, label: 'gas_sensor_kitchen'}, function (jsonData) {
        if (jsonData['update']) {
            $.get("getData.php?dev=gasSensor&label=gas_sensor_kitchen", function (data) {
                $("#power_kitchen_gas_sensor").html(data);
            });
        }
    }, "json");

}

$(document).ready(function () {


    // $(".rg_g_vault").buttonset();

    /* dev=label - событие = считать показания цифрового датчика */
    /* label=label_garage_door - имя датчика в базе = label_garage_door*/
    /* type=last - тип события = последнее показание */
    // $.get("getData.php?dev=label&label=label_garage_door&type=last", function (data) {
    //     $("#label_garage_door").html(data);
    // });

    // $.get("getData.php?dev=kitchenHood", function (data) {
    //     $("#power_kitchen_hood").html(data);
    // });



});

$(function () {

    power_updateAll();

    $("#power_kitchen_hood_update_info").button().click(function () {
        $.get("updateKitchenHoodInfo.php", function () {});
    });

});

$(document).everyTime("2s", function () {

    power_checkVent_Status();
    power_checkKitchenGasSensor_Status();

});
