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

function heater_updateDataScheme() {
    arrBlockHeaterSchemeID.forEach(function (item) {
        getDataSchemeSensor(item);
    })

    $.get("getData.php?dev=temp_delta&label1=temp_heater_boiler_out&label2=temp_heater_boiler_in", function (data) {
        $("#heater_temp_boiler_delta_data").html(data);
    });
    $.get("getData.php?dev=temp_delta&label1=temp_heater_floor_in&label2=temp_heater_floor_out", function (data) {
        $("#heater_temp_floor_delta_data").html(data);
    });

}

function heater_updateDataAll() {
    heater_updateDataScheme();
}

function getClickSensors(data) {
    //назначим подписку на клик для формирования графика
    var widget = data.widget;
    arrBlockHeaterSchemeID.forEach(function (item) {
        var selector = "#"+item[2];
        $(selector).off('click');
        widget.properties.label = item[0];
        $(selector).on('click', null, widget, _clickOnDataSensor);
    })
}

function setClickEvent(url) {
    $.getJSON(url, function (data) {
        var sensorData = data.data;
        var selector = '#' + sensorData.id;
        _setClickEventSensorData(selector, data.widget);
    });

}

$(document).ready(function () {
    heater_updateDataAll();

    $('.sensor_block').each(function () {
        var id = $(this).attr('id');
        var url = 'data/heater/' + id + '.json';
        setClickEvent(url);
    });


    $.getJSON("data/heater/heater_sensors_widget.json", function (data) {
        getClickSensors(data);
    });


});

//Обновление показания температуры каждые 5 минут
$(document).everyTime("300s", function () {
    heater_updateDataAll();
});
