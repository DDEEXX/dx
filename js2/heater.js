/**
 * 0 - label
 * 1 - id div - значение датчика
 * 2 - id div - блока датчика (для клика)
*/
var arrBlockHeaterSchemeID = [['temp_heater_boiler_out','heater_temp_boiler_out_data'],
    ['temp_heater_boiler_in', 'heater_temp_boiler_in_data'],
    ['temp_heater_floor_out', 'heater_temp_floor_out_data'],
    ['temp_heater_floor_in', 'heater_temp_floor_in_data'],
    ['temp_heater_sauna_out', 'heater_temp_sauna_out_data'],
    ['temp_heater_floor1_out', 'heater_temp_floor1_out_data'],
    ['temp_heater_floor2_out', 'heater_temp_floor2_out_data']];

function getDataSchemeSensor(item) {
    $.get("getData.php?dev=temp&label=" + item[0], function (data) {
        $("#" + item[1]).html(data);
    });
}

function heater_updateSchemeDelta() {
    $.get("getData.php?dev=temp_delta&label1=temp_heater_boiler_out&label2=temp_heater_boiler_in", function (data) {
        $("#heater_temp_boiler_delta_data").html(data);
    });
    $.get("getData.php?dev=temp_delta&label1=temp_heater_floor_in&label2=temp_heater_floor_out", function (data) {
        $("#heater_temp_floor_delta_data").html(data);
    });
}

function heater_updateDataScheme() {

    $('.sensor_block').each(function () {
        var url = 'data/heater/' + $(this).attr('id') + '.json';
        $.getJSON(url, function (data) {
            var sensorData = data.data;
            var selector = '#' + sensorData.id;
            var value = data.data.value;
            var sensorDigit = $(selector).find("." + value["classDigit"]);
            /* dev - тип событие, label - имя датчика в базе */
            $.get("getData.php?dev=" + value["dev"] + "&label=" + value["label"], function (data) {
                $(sensorDigit).html(data);
            });
        });
    });

    heater_updateSchemeDelta();
}

function heater_updateDataAll() {
    heater_updateDataScheme();
}

$(document).ready(function () {

    // класс для стиля показаний датчиков
    $('.sensor_block').each(function () {
        const url = 'data/heater/' + $(this).attr('id') + '.json';
        _getSensorProperties(url);
    });
    heater_updateSchemeDelta();

});

//Обновление показания температуры каждые 5 минут
$(document).everyTime("60s", function () {
    heater_updateDataAll();
});
