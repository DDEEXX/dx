const gasSensorsData = [
    {"id": "power_kitchen_gas_sensor", "label": "gas_sensor_kitchen", "title": "Кухня"},
]

function power_updateAll() {

    $.get("getData.php?dev=kitchenHood", function (data) {
        $("#power_kitchen_hood").html(data);
    });

    const pathConst = "getData.php?dev=gasSensor&label=";
    $.each(gasSensorsData,function(index, val) {
        const path = pathConst+val['label']+"&title="+val['title'];
        $.get(path, function (data) {
            $("#"+val['id']).html(data);
        })
    })
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
    $.each(gasSensorsData,function(index, val) {
        const path = "getData.php?dev=gasSensor&label="+val['label']+"&title="+val['title'];
        $.get(path, function (data) {
            //если время изменения на странице меньше чем время в пришедшем коде с сервера, обновляем
            const dataServer = $(data);
            const idSensor = "#"+val['id'];
            const idUpdateSensor = "#"+val['label']+"_last_update";
            const updateTimeClient = $(idSensor+" "+idUpdateSensor).val();
            const updateTimeServer = dataServer.find(idUpdateSensor).val();
            if (Number(updateTimeClient) < Number(updateTimeServer)) {
                $("#"+val['id']).html(data);
            }
        })
    })
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

$(document).everyTime("3s", function () {
    power_checkVent_Status();
    power_checkKitchenGasSensor_Status();
});
