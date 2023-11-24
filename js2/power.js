const gasSensorsData = [
    {
        "id": "power_kitchen_gas_sensor",
        "label": "gas_sensor_kitchen",
        "title": "Кухня",
        "dialogSetupContent": "#power_kitchen_gas_sensor_dialogSetup_content"
    },
]

function power_updateAll() {

    $.get("data/power/kitchenHood.php?dev=kitchenHood", function (data) {
        $("#power_kitchen_hood").html(data);
    });

    const pathConst = "data/power/gasSensors.php?dev=gasSensor&label=";
    $.each(gasSensorsData, function (index, val) {
        const path = pathConst + val['label'] + "&title=" + val['title'];
        $.get(path, function (data) {
            $("#" + val['id']).html(data);
        })
    })

    $.get("data/power/gate.php?dev=loadData", function (data) {
        $("#power_gate").html(data);
    });

}

function power_checkVent_Status() {
    const curDateStatus = $('#kitchen_hood_last_status').val();
    $.post("data/power/kitchenHood.php", {
        dev: "check_ventStatus",
        dateStatus: curDateStatus
    }, function (jsonData) {
        if (jsonData['update']) {
            $.get("data/power/kitchenHood.php?dev=kitchenHood", function (data) {
                $("#power_kitchen_hood").html(data);
            });
            $.get("data/power/kitchenHood.php?dev=info", function (data) {
                $("#power_kitchen_hood_dialogSetup_content").html(data);
            });
        } else {
            let arLabels = [];
            arLabels.push('light_kitchen_vent');
            $.post("modules/getData.php", {dev: "check_value", 'labels[]': arLabels}, function (jsonData) {
                let res = jsonData.find(i => i.label === 'light_kitchen_vent');
                if (res) {
                    const value = $('#power_kitchen_hood_light').attr("value");
                    if ((value === 'on' && res.value !== 1) ||
                        (value === 'off' && res.value === 1)) {
                        $.get("data/power/kitchenHood.php?dev=kitchenHood", function (data) {
                            $("#power_kitchen_hood").html(data);
                        });
                    }
                }
            }, "json");
        }
    }, "json");
}

function power_checkKitchenGasSensor_Status() {
    $.each(gasSensorsData, function (index, val) {
        const curDateStatus = $("#" + val['label'] + "_last_update").val();
        $.post("data/power/gasSensors.php", {
            dev: "check_gasSensorStatus",
            dateStatus: curDateStatus,
            label: val['label']
        }, function (jsonData) {
            if (jsonData['update']) {
                $.get("data/power/gasSensors.php?dev=gasSensor&label=" + val['label'] + "&title=" + val['title'], function (data) {
                    $("#" + val['id']).html(data);
                })
                const power_kitchen_gas_sensor_dialogSetup_content = val['dialogSetupContent'];
                $.get("data/power/gasSensors.php?dev=dialogSetupContent&label=" + val['label'], function (data) {
                    $(power_kitchen_gas_sensor_dialogSetup_content).html(data);
                });
            } else {
                const path = "data/power/gasSensors.php?dev=gasSensor&label=" + val['label'] + "&title=" + val['title'];
                $.get(path, function (data) {
                    //если время изменения на странице меньше чем время в пришедшем коде с сервера, обновляем
                    const dataServer = $(data);
                    const idSensor = "#" + val['id'];
                    const idUpdateSensor = "#" + val['label'] + "_last_update";
                    const updateTimeClient = $(idSensor + " " + idUpdateSensor).val();
                    const updateTimeServer = dataServer.find(idUpdateSensor).val();
                    if (Number(updateTimeClient) < Number(updateTimeServer)) {
                        $("#" + val['id']).html(data);
                    }
                })
            }
        })
    })
}

$(function () {
    power_updateAll();

    const power_kitchen_hood_dialogSetup = "#power_kitchen_hood_dialogSetup";
    $(power_kitchen_hood_dialogSetup).dialog({
        autoOpen: false,
        draggable: false,
        position: {my: "center", at: "center", of: "#page_power"},
        resizable: false,
        title: "Настройка вытяжки",
        height: "auto",
        width: 800,
        open: function (event, ui) {
            $.get("data/power/kitchenHood.php?dev=info", function (data) {
                $("#power_kitchen_hood_dialogSetup_content").html(data);
            });
        }
    });

    $("#power_kitchen_hood_setup").button({
        icon: "ui-icon-gear",
        showLabel: false
    }).click(function () {
        $(power_kitchen_hood_dialogSetup).dialog("open");
    });

    // GAS SENSOR
    const power_kitchen_gas_sensor_dialogSetup = "#power_kitchen_gas_sensor_dialogSetup";
    const label = "gas_sensor_kitchen";
    const power_kitchen_gas_sensor_dialogSetup_content = "#power_kitchen_gas_sensor_dialogSetup_content";
    const power_kitchen_gas_sensor_setup = "#power_kitchen_gas_sensor_setup"
    $(power_kitchen_gas_sensor_dialogSetup).dialog({
        autoOpen: false,
        draggable: false,
        position: {my: "center", at: "center", of: "#page_power"},
        resizable: false,
        title: "Настройка газового датчика на кухне",
        height: "auto",
        width: 800,
        open: function (event, ui) {
            $.get("data/power/gasSensors.php?dev=dialogSetupContent&label=" + label, function (data) {
                $(power_kitchen_gas_sensor_dialogSetup_content).html(data);
            });
        }
    });

    $(power_kitchen_gas_sensor_setup).button({
        icon: "ui-icon-gear",
        showLabel: false
    }).click(function () {
        $(power_kitchen_gas_sensor_dialogSetup).dialog("open");
    });

});

$(document).everyTime("3s", function () {
    power_checkVent_Status();
    power_checkKitchenGasSensor_Status();
});
