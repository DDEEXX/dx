const lampsData = [
    {"id": "light_plan_light_cabinet", "label": "light_cabinet", "payload": "pulse", "type": "light", "place": "65;160"},
    {"id": "light_plan_cabinet_table", "label": "backlight_cabinet_table", "payload": "", "type": "backlight", "place": "80;210"},
    {"id": "light_plan_light_dining", "label": "light_dining", "payload": "pulse", "type": "light", "place": "225;110"},
    {"id": "light_plan_floor_2_backlight", "label": "light_hol_2_n", "payload": "", "type": "backlight", "place": "250;635"},
    // {"id": "light_plan_under_stair", "label": "light_under_stair", "payload": "", "type": "backlight"},
    {"id": "light_plan_stair", "label": "light_stair", "payload": "", "type": "backlight", "place": "115;255"},
    {"id": "light_plan_backlight_bathroom", "label": "backlight_bathroom", "payload": "", "type": "backlight", "place": "285;745"},
    {"id": "light_plan_light_floor_2", "label": "light_floor_2", "payload": "pulse", "type": "light", "place": "215;685"},
    // {"id": "light_plan_backlight_understair", "label": "backlight_understair", "payload": "", "type": "backlight"},
    {"id": "light_plan_backlight_kitchen", "label": "backlight_kitchen", "payload": "", "type": "backlight", "place": "290;190"},
    // {"id": "light_plan_light_kitchen_vent", "label": "light_kitchen_vent", "payload": "", "type": "backlight"},
    {"id": "light_plan_light_kitchen", "label": "light_kitchen", "payload": "pulse", "type": "light", "place": "240;180"},
    {"id": "light_plan_light_hallway", "label": "light_hallway", "payload": "pulse", "type": "light", "place": "230;255"},
    {"id": "light_plan_light_heater", "label": "light_heater", "payload": "pulse", "type": "light", "place": "175;330"},
    {"id": "light_plan_light_toilet", "label": "light_toilet", "payload": "pulse", "type": "light", "place": "165;265"},
    {"id": "light_plan_light_garage", "label": "light_garage", "payload": "pulse", "type": "light", "place": "65;315"},
    {"id": "light_plan_light_hall_1", "label": "light_hall_1", "payload": "pulse", "type": "light", "place": "165;70"},
    {"id": "light_plan_light_hall_2", "label": "light_hall_2", "payload": "", "type": "light", "place": "165;115"},
    {"id": "light_plan_light_bedroom_Tima", "label": "light_bedroom_Tima", "payload": "pulse", "type": "light", "place": "365;640"},
    {"id": "light_plan_light_bedroom", "label": "light_bedroom", "payload": "pulse", "type": "light", "place": "255;520"},
    {"id": "light_plan_light_bathroom", "label": "light_bathroom", "payload": "pulse", "type": "light", "place": "235;755"},
    {"id": "light_plan_light_terrace", "label": "light_terrace", "payload": "pulse", "type": "light", "place": "85;15"},
    {"id": "light_plan_backlight_bedroom", "label": "backlight_bedroom", "payload": "", "type": "backlight", "place": "310;545"},
    {"id": "light_plan_light_bedroom_Lera", "label": "light_bedroom_Lera", "payload": "pulse", "labelSensor": "sensor_light_bedroom_Lera", "type": "light", "place": "175;600"}
]

function light_plan_updateAll() {

    $.each(lampsData,function(index, val) {
        const id = val['id'];
        const label = val['label'];
        const place = val['place'];
        const type = val['type'];
        const payload = val['payload']===""?"":("&payload="+val['payload']);
        const sensor = val['labelSensor']?("&labelSensor="+val['labelSensor']):"";
        const path = "getData.php?dev=light&label="+label+"&place="+place+"&img="+type+payload+sensor;
        $.get(path, function (data) {
            $("#"+id).html(data);
        })
    })

}

function light_plan_setEvent() {

    $(".light_plan_lamp_click_on_off").on("click", ".light_plan_lamp_click",  function () {
        const lamp = $(this);
        const label = lamp.attr("label");
        const value = lamp.attr("payload");
        $.get("powerKey.php?label="+label+"&value="+value+"&status=web", function () {});
    });

}

function light_plan_checkLampStatus() {

    let arLabels = [];

    $('#light_plan').find('.light_plan_lamp_click').each(function () {
        let curLabel = $(this).attr("label");
        if ($(this).attr("labelSensor")) {
            curLabel = $(this).attr("labelSensor");
        }
        arLabels.push(curLabel);
    });

    $.post("getData.php", {dev: "check_value", 'labels[]': arLabels}, function (jsonData) {
        $('#light_plan').find('.light_plan_lamp_click').each(function (i, el) {
            const lamp = $(el);
            const label = lamp.attr("label");
            const labelSensor = lamp.attr("labelSensor");
            const value = lamp.attr("value");
            const lampData = lampsData.find(i=>i.label === label);
            if (lampData) {
                let res;
                if (labelSensor) {
                    res = jsonData.find(i => i.label === labelSensor);
                } else {
                    res = jsonData.find(i => i.label === label);
                }
                if (res) {
                    if ((value === 'on' && res.value !== 1) ||
                        (value === 'off' && res.value === 1)) {
                        const id = lampData.id;
                        const sensor = lampData.labelSensor?("&labelSensor="+lampData.labelSensor):"";
                        const payload = lampData.payload === "" ? "" : ("&payload=" + lampData.payload);
                        const place = lampData.place;
                        const type = lampData.type;
                        const path = "getData.php?dev=light&label="+label+"&place="+place+"&img="+type+payload+sensor;
                        $.get(path, function (data) {
                            $("#" + id).html(data);
                        })
                    }
                }
            }
        });
    }, "json");
}

$(document).ready(function () {

    light_plan_updateAll();
    light_plan_setEvent();

});

$(document).everyTime("1s", function () {

    light_plan_checkLampStatus();

});
