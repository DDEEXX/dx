function heater_updateSchemeDelta() {
    $.get("getData.php?dev=temp_delta&label1=temp_heater_boiler_out&label2=temp_heater_boiler_in", function (data) {
        $("#heater_temp_boiler_delta_data").html(data);
    });
    $.get("getData.php?dev=temp_delta&label1=temp_heater_floor_in&label2=temp_heater_floor_out", function (data) {
        $("#heater_temp_floor_delta_data").html(data);
    });
}

function heater_updateDataScheme() {
    _updateSensorsData('sensor_block', 'data/heater/');
    heater_updateSchemeDelta();
}

function heater_updateBoiler() {
    $.get('data/heater/heating.php?dev=boiler&label=boiler_opentherm', function (data) {
        $('#heater_boiler_last_status').val(data.date);
        $('#boiler_ch').html(data.ch + " &degC");
        $('#boiler_retb').html(data.retb + " &degC");
        $('#boiler_tset').html(data.tset + " &degC");
        $('#boiler_dhw').html(data.dhw + " &degC");
        $('#boiler_spdhw').html(data.spdhw + " &degC");
        if (data.chon && data.flon) {
            $('#boiler_heating_fire').attr('src', 'img2/icon_small/fire.png')
            $('#boiler_heating_fire_level').html(data.mlev + '%');
        } else {
            $('#boiler_heating_fire').attr('src', 'img2/icon_small/fire_.png')
            $('#boiler_heating_fire_level').html("");
        }
        if (data.dhwon && data.flon) {
            $('#boiler_heating_wfire').attr('src', 'img2/icon_small/fire.png')
            $('#boiler_heating_wfire_level').html(data.mlev + '%');
        } else {
            $('#boiler_heating_wfire').attr('src', 'img2/icon_small/fire_.png')
            $('#boiler_heating_wfire_level').html("");
        }
        $('#boiler_room').html(data.room + " &degC");
        $('#boiler_out').html(data.out + " &degC");
    });
}

function heater_checkBoiler_Status() {
    const curDateStatus = $('#heater_boiler_last_status').val();
    $.post("data/heater/heating.php", {
        dev: "check_boilerStatus",
        dateStatus: curDateStatus,
        label: "boiler_opentherm"
    }, function (jsonData) {
        if (jsonData['update']) {
            heater_updateBoiler();
        }
    }, "json");
}

$(function () {

    $("#heater_boiler_heating").slider({
        min: 190,
        max: 280,
        create: function (event, ui) {
            let th = $(this);
            $.get('data/heater/heating.php?dev=boiler&label=boiler_opentherm', function (data) {
                let spr10 = Math.round(data._spr * 10);
                th.slider("value", spr10);
                $('#boiler_spr').html(spr10 / 10 + " &degC");
            });
        },
        slide: function (event, ui) {
            $('#boiler_spr').html(ui.value / 10 + " &degC");
        },
        change: function( event, ui ) {
            $.get('data/heater/heating.php?dev=set&label=boiler_opentherm&p=_spr&v='+ui.value+'&d=10', function () {});
        }
    });

    $("#heater_floor_1").slider({
        min: 230,
        max: 400,
        create: function (event, ui) {
            $(this).slider("value", 290);
            $('#boiler_sprf1').html(290 / 10 + " &degC");
        },
        slide: function (event, ui) {
            $('#boiler_sprf1').html(ui.value / 10 + " &degC");
        }
    });
    $("#heater_floor_bathroom").slider({
        min: 220,
        max: 280,
        create: function (event, ui) {
            $(this).slider("value", 240);
            $('#boiler_sprb').html(240 / 10 + " &degC");
        },
        slide: function (event, ui) {
            $('#boiler_sprb').html(ui.value / 10 + " &degC");
        }
    });
    $("#heater_boiler_water").slider({
        min: 35,
        max: 60,
        create: function (event, ui) {
            let th = $(this);
            $.get('data/heater/heating.php?dev=boiler&label=boiler_opentherm', function (data) {
                let dhw = Number(data._dhw);
                th.slider("value", dhw);
                $('#boiler_sprw').html(dhw + " &degC");
            });
        },
        slide: function (event, ui) {
            $('#boiler_sprw').html(ui.value + " &degC");
        },
        change: function( event, ui ) {
            $.get('data/heater/heating.php?dev=set&label=boiler_opentherm&p=_dhw&v='+ui.value, function () {});
        }
    });
    heater_updateBoiler();

    // класс для стиля показаний датчиков
    $('.sensor_block').each(function () {
        const url = 'data/heater/' + $(this).attr('id') + '.json';
        _getSensorProperties(url);
    });
    heater_updateSchemeDelta();

});

//Обновление показания температуры каждые 5 минут
$(document).everyTime("60s", function () {
    heater_updateDataScheme();
});

// $(document).everyTime("15s", function () {
//     heater_updateBoiler();
// });

$(document).everyTime("3s", function () {
    heater_checkBoiler_Status();
});

