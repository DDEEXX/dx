const lampsData = [
    {"id": "light_tile_light_cabinet", "label": "light_cabinet", "payload": "pulse"},
    {"id": "light_tile_cabinet_table", "label": "backlight_cabinet_table", "payload": ""},
    {"id": "light_tile_light_dining", "label": "light_cabinet", "payload": "pulse"},
    {"id": "light_tile_floor_2_backlight", "label": "light_hol_2_n", "payload": ""},
    {"id": "light_tile_under_stair", "label": "light_cabinet", "payload": ""},
    {"id": "light_tile_stair", "label": "light_stair", "payload": ""},
    {"id": "light_tile_backlight_bathroom", "label": "backlight_bathroom", "payload": ""},
    {"id": "light_tile_light_floor_2", "label": "light_floor_2", "payload": "pulse"},
    {"id": "light_tile_backlight_understair", "label": "backlight_understair", "payload": ""},
    {"id": "light_tile_backlight_kitchen", "label": "backlight_kitchen", "payload": ""},
    {"id": "light_tile_light_kitchen_vent", "label": "light_kitchen_vent", "payload": ""},
    {"id": "light_tile_light_kitchen", "label": "light_kitchen", "payload": "pulse"}
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
        const path = "getData.php?dev=light_tile&label="+val['label']+payload;
        $.get(path, function (data) {
            $("#"+val['id']).html(data);
        })
    })

}

function light_tile_checkLampStatus() {
    let labels = [];

    $('#light_tile').find('.light_tile_lamp_click').each(function () {
        const label = $(this).attr("label");
        labels.push(label);
    });

    $.post("getData.php", {dev: "check_value", 'labels[]': labels}, function (jsonData) {
        const data = jsonData;
        $('#light_tile').find('.light_tile_lamp_click').each(function () {
            const lamp = $(this);
            const label = lamp.attr("label");
            const value = lamp.attr("value");
            const lampData = lampsData.find(i=>i.label = label);
            if (lampData) {
                const id = lampData.id;
                const payload = lampData.payload === "" ? "" : ("&payload=" + lampData.payload);
                const res = data.find(i => i.label === label);
                if (res) {
                    if ((value === 'on' && res.value !== 1) ||
                        (value === 'off' && res.value === 1)) {
                        $.get("getData.php?dev=light_tile&label=" + label + payload, function (data) {
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

    $(".light_button_setup").button();
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

    //light_tile_updateAll();

    //Plan
    // $.get("getData.php?dev=light&label=light_hol_2_n&type=last&place=250;635&img=backlight", function (data) {
    //     $("#light_lamp2").html(data);
    // });
    //
    // $.get("getData.php?dev=light&label=light_stairs_3&type=last&place=220;685&img=backlight", function (data) {
    //     $("#light_lamp3").html(data);
    // });
    //
    // $.get("getData.php?dev=light&label=bathroom_mirror_light&type=last&place=295;735&img=backlight", function (data) {
    //     $("#light_lamp10").html(data);
    // });


});
