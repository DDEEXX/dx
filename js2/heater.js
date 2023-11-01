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
        $('#boiler_tset').html(data.tset + " &degC");
        $('#boiler_dhw').html(data.dhw + " &degC");
        $('#boiler_spdhw').html(data.spdhw + " &degC");

        //модуляция горелки
        $levelFire = data.flon && (data.chon || data.flon) ? data.mlev + '%' : '';
        //иконка горелки СО
        $scrFire = data.chon ? (data.flon ? 'img2/icon_small/fire.png' : 'img2/icon_small/fire2.png')
            : 'img2/icon_small/fire2.png';
        //иконка горелки ГВС
        $scrFireWater = data.dhwon ? (data.flon ? 'img2/icon_small/fire.png' : 'img2/icon_small/fire2.png')
            : 'img2/icon_small/fire2.png';
        $('#boiler_heating_fire').attr('src', $scrFire);
        $('#boiler_heating_fire_level').html(data.chon && data.flon ? $levelFire : "");
        $('#boiler_heating_wfire').attr('src', $scrFireWater);
        $('#boiler_heating_wfire_level').html(data.dhwon && data.flon ? $levelFire : "");

        $('#boiler_room').html(data.room.toFixed(1) + " &degC");
        $('#boiler_out').html(data.out.toFixed(1) + " &degC");
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

    $("#boiler_power, #boiler_power_water").checkboxradio({
        icon: false
    }).click(function () {
        const p = $(this).attr("property");
        const v = $(this).is(":checked");
        $.get('data/heater/heating.php?dev=set&label=boiler_opentherm&p=' + p + '&v=' + v);
    });

    $("#boiler_power_floor").checkboxradio({
        icon: false
    }).click(function () {
        const p = $(this).attr("property");
        const v = $(this).is(":checked");
        $.get('data/heater/heating.php?dev=setProperty&mode=one&property=' + p + '&value=' + v);
    });


    const label = "boiler_opentherm";
    $("#heater_boiler_setup_dialog_").dialog({
        autoOpen: false,
        draggable: false,
        position: {my: "center", at: "center", of: "#page_heater"},
        resizable: false,
        title: "Настройка отопления в доме",
        height: "auto",
        width: 1100,
        open: function (event, ui) {
            $.get("data/heater/heating.php?dev=dialogSetup&label=" + label, function (data) {
                $("#heater_boiler_setup_dialog_content").html(data);
                $("#heater_boiler_setup_dialog_").dialog( "option", "position", {my: "center", at: "center", of: "#page_heater"})
            });
        }
    });

    $("#heater_boiler_setup").button().click(function () {
        $("#heater_boiler_setup_dialog_").dialog("open");
    });

    $("#heater_boiler_log_dialog").dialog({
        autoOpen: false,
        draggable: false,
        position: {my: "center", at: "center", of: "#page_heater"},
        resizable: false,
        title: "Лог отопления в доме",
        height: "auto",
        width: 1100,
        open: function (event, ui) {
            $.get("data/heater/heating.php?dev=heatingLog&type=bl", function (data) {
                $("#heater_boiler_log_dialog_content").html(data);
                $("#heater_boiler_log_dialog").dialog( "option", "position", {my: "center", at: "center", of: "#page_heater"})
            });
        }
    });
    $("#heater_boiler_log").button({
        icon : 'ui-icon-signal'
    }).click(function () {
        $("#heater_boiler_log_dialog").dialog("open");
    });

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
        stop: function (event, ui) {
            $.get('data/heater/heating.php?dev=set&label=boiler_opentherm&p=_spr&v=' + ui.value + '&d=10');
        }
    });
    $("#heater_floor_1").slider({
        min: 230,
        max: 400,
        create: function (event, ui) {
            let th = $(this);
            $.get('data/heater/heating.php?dev=pid', function (data) {
                let spr10 = Math.round(data.f_spr * 10);
                th.slider("value", spr10);
                $('#boiler_sprf1').html(spr10 / 10 + " &degC");
            });
        },
        slide: function (event, ui) {
            $('#boiler_sprf1').html(ui.value / 10 + " &degC");
        },
        stop: function (event, ui) {
            $.get('data/heater/heating.php?dev=setProperty&mode=one&property=f_spr&value=' + ui.value + '&d=10');
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
        stop: function (event, ui) {
            $.get('data/heater/heating.php?dev=set&label=boiler_opentherm&p=_dhw&v=' + ui.value, function () {
            });
        }
    });
    heater_updateBoiler();

    // класс для стиля показаний датчиков
    $('.sensor_block').each(function () {
        const url = 'data/heater/' + $(this).attr('id') + '.json';
        _getSensorProperties(url);
    });
    heater_updateSchemeDelta();

    //события на подгружаемые элементы - режим работы
    $('#heater_boiler_setup_dialog_content').on("change", 'input[name="boiler_mode_radio"]' ,function() {
        const value = $(this).val();
        $.get('data/heater/heating.php?dev=set&label=boiler_opentherm&p=_mode&v=' + value);
    });

    $('#heater_boiler_setup_dialog_content').on("change", 'input[name="boiler_floor_mode_radio"]' ,function() {
        const value = $(this).val();
        $.get("data/heater/heating.php?dev=setProperty&mode=one&property=f_mode&value="+value, function () {
            $.get("data/heater/heating.php?dev=dialogSetup&label=" + label, function (data) {
                $("#heater_boiler_setup_dialog_content").html(data);
            });
        });
    });

    $('#heater_boiler_setup_dialog_content').on("click", '#boiler_setup_save_options' ,function() {
        let data = {};
        $('#heater_boiler_setup_dialog_content').find('[property]').each(function(i, el) {
            const p = $(this).attr('property');
            const v = $(this).val();
            data[p] = v;
        });
        data['f_mode'] = $('#heater_boiler_setup_dialog_content').find('input[name="boiler_floor_mode_radio"]:checked').val();

        const jsonDana = JSON.stringify(data);
        $.post("data/heater/heating.php", {dev: "setProperty", data: jsonDana} );
    });

});

$(document).ready(function () {
    $.get('data/heater/heating.php?dev=boiler&label=boiler_opentherm', function (data) {
        $("#boiler_power").attr("checked", data._chena).checkboxradio("refresh");
        $("#boiler_power_water").attr("checked", data._dhwena).checkboxradio("refresh");
    });
    $.get('data/heater/heating.php?dev=pid', function (data) {
        $("#boiler_power_floor").attr("checked", data.f_pwr).checkboxradio("refresh");
    });
});

//Обновление показания температуры каждые 5 минут
$(document).everyTime("60s", function () {
    heater_updateDataScheme();
});

$(document).everyTime("3s", function () {
    heater_checkBoiler_Status();
});

