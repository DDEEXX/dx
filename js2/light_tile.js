const lampsData = [
    {"id": "light_tile_light_cabinet", "label": "light_cabinet", "payload": "pulse"},
    {"id": "light_tile_cabinet_table", "label": "backlight_cabinet_table", "payload": ""},
    {"id": "light_tile_light_dining", "label": "light_dining", "payload": "pulse"},
    {"id": "light_tile_floor_2_backlight", "label": "light_hol_2_n", "payload": ""},
    {"id": "light_tile_under_stair", "label": "light_under_stair", "payload": ""},
    {"id": "light_tile_stair", "label": "light_stair", "payload": ""},
    {"id": "light_tile_backlight_bathroom", "label": "backlight_bathroom", "payload": ""},
    {"id": "light_tile_light_floor_2", "label": "light_floor_2", "payload": "pulse"},
    {"id": "light_tile_backlight_understair", "label": "backlight_understair", "payload": ""},
    {"id": "light_tile_backlight_kitchen", "label": "backlight_kitchen", "payload": ""},
    {"id": "light_tile_light_kitchen_vent", "label": "light_kitchen_vent", "payload": ""},
    {"id": "light_tile_light_kitchen", "label": "light_kitchen", "payload": "pulse"},
    {"id": "light_tile_light_hallway", "label": "light_hallway", "payload": "pulse"},
    {"id": "light_tile_light_heater", "label": "light_heater", "payload": "pulse"},
    {"id": "light_tile_light_toilet", "label": "light_toilet", "payload": "pulse"},
    {"id": "light_tile_light_garage", "label": "light_garage", "payload": "pulse"},
    {"id": "light_tile_light_hall_1", "label": "light_hall_1", "payload": "pulse"},
    {"id": "light_tile_light_hall_2", "label": "light_hall_2", "payload": ""},
    {"id": "light_tile_light_bedroom_Tima", "label": "light_bedroom_Tima", "payload": "pulse"},
    {"id": "light_tile_light_bedroom", "label": "light_bedroom", "payload": "pulse"},
    {"id": "light_tile_light_bathroom", "label": "light_bathroom", "payload": "pulse"},
    {"id": "light_tile_light_terrace", "label": "light_terrace", "payload": "pulse"},
    {"id": "light_tile_backlight_bedroom", "label": "backlight_bedroom", "payload": ""},
    {"id": "light_tile_light_bedroom_Lera", "label": "light_bedroom_Lera", "payload": "pulse", "labelSensor": "sensor_light_bedroom_Lera"},
    {"id": "light_tile__garland_home_1", "label": "garland_home_1", "payload": ""},
    {"id": "light_tile__garland_home_2", "label": "garland_home_2", "payload": ""}
]

function light_tile_setEvent() {

    $(".light_tile_click_on_off").on("click", ".light_tile_lamp_click", function () {
        const lamp = $(this);
        const label = lamp.attr("label");
        const value = lamp.attr("payload");
        $.get("powerKey.php?label="+label+"&value="+value+"&status=web", function () {});
    });

}

function light_tile_updateAll() {

    $.each(lampsData,function(index, val) {
        const payload = val['payload']===""?"":("&payload="+val['payload']);
        const sensor = val['labelSensor']?("&labelSensor="+val['labelSensor']):"";
        const path = "getData.php?dev=light_tile&label="+val['label']+payload+sensor;
        $.get(path, function (data) {
            $("#"+val['id']).html(data);
        })
    })

}

function light_tile_checkLampStatus() {

    let arLabels = [];

    $('#light_tile').find('.light_tile_lamp_click').each(function () {
        let curLabel = $(this).attr("label");
        if ($(this).attr("labelSensor")) {
            curLabel = $(this).attr("labelSensor");
        }
        arLabels.push(curLabel);
    });

    $.post("getData.php", {dev: "check_value", 'labels[]': arLabels}, function (jsonData) {
        $('#light_tile').find('.light_tile_lamp_click').each(function (i, el) {
            const lamp = $(el);
            const label = lamp.attr("label");
            const labelSensor = lamp.attr("labelSensor");
            const value = lamp.attr("value");
            const lampData = lampsData.find(i=>i.label === label);
            if (lampData) {
                const id = lampData.id;
                const payload = lampData.payload === "" ? "" : ("&payload=" + lampData.payload);
                let res;
                if (labelSensor) {
                    res = jsonData.find(i => i.label === labelSensor);
                } else {
                    res = jsonData.find(i => i.label === label);
                }
                if (res) {
                    if ((value === 'on' && res.value !== 1) ||
                        (value === 'off' && res.value === 1)) {
                        const sensor = lampData.labelSensor?("&labelSensor="+lampData.labelSensor):"";
                        $.get("getData.php?dev=light_tile&label=" + label + payload + sensor, function (data) {
                            $("#" + id).html(data);
                        })
                    }
                }
            }
        });
    }, "json");
}

$(document).ready(function () {

    light_tile_updateAll();
    light_tile_setEvent();

    $("#backlight_hall .button").button( {showLabel: false} );
    $('#backlight_hall .button').click(function () {
        const value = $(this).attr("value");
        $.get("powerKey.php?label=backlight_first_floor&value=" + value, function () {});
    });
    $( "#backlight_hall_slider" ).slider({
        min: 0,
        max: 8,
        slide: function( event, ui ) {
            let value = ui.value;
            if (value === 0) {value = 8} //min
            else if (value === 8) {value = 9} //max
            $.get("powerKey.php?label=backlight_first_floor&value=" + value, function () {});
        }
    });

});

$(document).everyTime("1s", function () {

    light_tile_checkLampStatus();

});
