var arrBlockID = ['block_weather_outdoor',
    'block_weather_hall',
    'block_weather_server',
    'block_weather_bedroom',
    'block_weather_bedroom_Lera',
    'block_weather_bathroom'];

function weather_getWidget(idBlock, idBlockClick) {
    var selector = "#"+idBlock;
    var url = 'data/weather/widget/' + idBlock + '.json';
    $(selector).off('click');
    getWidgetSensor(idBlock, idBlockClick, url);
    $(selector).on('click', function () {
        weather_loadBlockData(idBlock)
    });
}

function weather_clickOnDataSensor() {
    /*параметры виджетов в виде JSON хранятся в файле, имя файла совпадает с id родительского блока с атрибутом isBlock="true"*/
    var idBlock = $(this).parent().parent().parent().attr('id');
    var idBlockClick = $(this).attr('id');
    weather_getWidget(idBlock, idBlockClick);
}

/* загрузка в блок на странице, данных датчиков (удаляет предыдущее события на click и назначение новое)
 * @param {string} idBlock
 */
function weather_loadBlockData(idBlock) {
    var selector = "#" + idBlock;
    $(selector).off('click');
    $(selector).load('data/weather/load/' + idBlock + '.html', idBlock, function (response, status) {
        if (status === 'success') {
            $(selector).on('click', '.expand', weather_clickOnDataSensor);
            var url = 'data/weather/widget/' + idBlock + '.json';
            getDataSensor(url);
        }
    });
}

function weather_updateDataAll() {
    arrBlockID.forEach(function (item) {
        var url = 'data/weather/widget/' + item + '.json';
        getDataSensor(url);
    })
}

function weather_updateTemperaturePlan() {
    $.get("getData.php?dev=temp&label=temp_hall&color=plan", function (data) {
        $("#weather_plan_temp_hall_data").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_stair&color=plan", function (data) {
        $("#weather_plan_temp_server_data").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_bedroom&color=plan", function (data) {
        $("#weather_plan_temp_bedroom_data").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_bedroom_Lera&color=plan", function (data) {
        $("#weather_plan_temp_bedroomLera_data").html(data);
    });
    $.get("getData.php?dev=temp&label=temp_bathroom&color=plan", function (data) {
        $("#weather_plan_temp_bathroom_data").html(data);
    });
    $.get("getData.php?dev=humidity&label=bathroom_humidity&color=plan", function (data) {
        $("#weather_plan_humidity_bathroom_data").html(data);
    });
}

function weather_loadPlan() {
    $("#weather_content").load('data/weather/load/weather_plan.html', function (){
        weather_loadBlockData('block_weather_outdoor');
        weather_updateTemperaturePlan();
    });
}

function weather_checkPlan() {
    return $("#weather_plan").length;
}

function weather_checkData() {
    return $("#weather_data").length;
}

function weather_getAllWidget() {
    weather_getWidget("block_weather_outdoor", "weather_temperature_out_block");
    weather_getWidget("block_weather_hall", "weather_temperature_hall_block");
    weather_getWidget("block_weather_server", "weather_temperature_server_block");
    weather_getWidget("block_weather_bedroom", "weather_temperature_bedroom_block");
    weather_getWidget("block_weather_bedroom_Lera", "weather_temperature_bedroomLera_block");
    weather_getWidget("block_weather_bathroom", "weather_temperature_bathroom_block");
}

function weather_getAllData() {
    arrBlockID.forEach(function (item) {
        weather_loadBlockData(item);
    })
}

function weather_loadAllData() {
    $("#weather_content").load('data/weather/load/weather_data.html', function () {
        weather_getAllData();
    });
}

function weather_loadAllWidget() {
    $("#weather_content").load('data/weather/load/weather_data.html', function () {
        weather_getAllWidget();
    });
}

function weather_ButtonWeatherClick() {
    $("#weather_button_123").click(function () {
        if (!weather_checkData()) {
            weather_loadAllData();
        } else {
            weather_getAllData();
        }
    })
    $("#weather_button_graph").click(function () {
        if (!weather_checkData()) {
            weather_loadAllWidget();
        } else {
            weather_getAllWidget()
        }
    })
    $("#weather_button_plan").click(function () {
        if (!weather_checkPlan()) {
            weather_loadPlan();
        }
    })
}

$(document).ready(function () {
    $.get("weather.php", function (data) {
        $("#weather_forecast").html(data)
    });
    weather_ButtonWeatherClick();
    weather_loadPlan();
})

//Обновление прогноза погоды 1 раз в час
$(document).everyTime("3600s", function () {
    $.get("weather.php", function (data) {
        $("#weather_forecast").html(data)
    });
});

//Обновление показания погоды каждые 10 минут
$(document).everyTime("600s", function () {
    weather_updateDataAll();
});

$(function () {
    $(".weather_button_setup").button();
})