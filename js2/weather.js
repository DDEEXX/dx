var arrBlockID = ['block_weather_outdoor',
    'block_weather_hall',
    'block_weather_server',
    'block_weather_bedroom',
    'block_weather_bedroom_Lera',
    'block_weather_bathroom'];

function getElementWait() {
    return $('<span class="weather_widget_wait"></span>');
}

function getSensorWidget(idBlock, data) {
    var selector = '#' + idBlock;
    $(selector).off('click');
    $(selector).html(data);
    $(selector).on('click', function () {
        loadBlockData(idBlock);
    });
}

function getWidget(idBlock, idBlockClick) {
    $("#" + idBlock).html(getElementWait());
    var url = 'data/weather/widget/' + idBlock + '.json';
    $.getJSON(url, function (data) {
        var widgetParam = data['widget'][idBlockClick];
        $.get("graphWidget.php", widgetParam, function (data) {
            getSensorWidget(idBlock, data);
        });
    })
}

function clickOnDataSensor() {
    /*параметры виджетов в виде JSON хранятся в файле, имя файла совпадает с id родительского блока с атрибутом isBlock="true"*/
    var idBlock = $(this).parentsUntil("div[isBlock=\"true\"]").parent().attr('id');
    var idBlockClick = $(this).attr('id');
    getWidget(idBlock, idBlockClick);
}

function updateDataJSON(data) { //получить данные, параметры показаний в data.data
    data.data.forEach(function (item) {
        /* dev - событие */
        /* label - имя датчика в базе */
        $.get("getData.php?dev=" + item["dev"] + "&label=" + item["label"], function (data) {
            $("#" + item["id"]).html(data);
        });
    });
}

function updateData(idBlock) { //получить параметры для отображения показаний из json файла
    var url = 'data/weather/widget/' + idBlock + '.json';
    $.getJSON(url, updateDataJSON);
}

/** загрузка в блок на странице, данных датчиков (удаляет пред. события на click и назначение новое)
 * @param {string} idBlock
 */
function loadBlockData(idBlock) {
    var selector = "#" + idBlock;
    $(selector).off('click');
    $(selector).load('data/weather/load/' + idBlock + '.html', idBlock, function (response, status) {
        if (status === 'success') {
            $(selector).on('click', '.expand', clickOnDataSensor);
            updateData(idBlock); //функция для обновления показаний датчиков в блоке
        }
    });
}

function updateDataAll() {
    arrBlockID.forEach(function (item) {
        updateData(item);
    })
}

function loadWeatherData() {
    arrBlockID.forEach(function (item) {
        loadBlockData(item);
    })
}

function afterLoadWeatherData() {
    loadWeatherData();
}

function loadData(updateData) {
    $("#weather_content").load('data/weather/load/weather_data.html', function () {
        if (updateData) {
            afterLoadWeatherData();
        } else {
            getAllWeatherWidget();
        }
    });
}

function updateWeatherTemperaturePlan() {
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

function afterLoadWeatherPlan() {
    updateWeatherTemperaturePlan();
}

function loadPlan() {
    $("#weather_content").load('data/weather/load/weather_plan.html', function (){
        afterLoadWeatherPlan();
    });
}

function checkWeatherPlan() {
    return $("#weather_plan").length;
}

function checkWeatherData() {
    return $("#weather_data").length;
}

function getAllWeatherWidget() {
    getWidget("block_weather_outdoor", "weather_temperature_out_block");
    getWidget("block_weather_hall", "weather_temperature_out_block");
    getWidget("block_weather_server", "weather_temperature_server_block");
    getWidget("block_weather_bedroom", "weather_temperature_bedroom_block");
    getWidget("block_weather_bedroom_Lera", "weather_temperature_bedroomLera_block");
    getWidget("block_weather_bathroom", "weather_temperature_bathroom_block");
}

function setButtonWeatherClick() {
    $("#weather_button_123").click(function () {
        if (!checkWeatherData()) {
            loadData(true);
        }
        arrBlockID.forEach(function (item) {
            loadBlockData(item);
        })
    })
    $("#weather_button_graph").click(function () {
        if (!checkWeatherData()) {
            loadData(false);
        } else {
            getAllWeatherWidget()
        }
    })
    $("#weather_button_plan").click(function () {
        if (!checkWeatherPlan()) {
            loadPlan();
        }
    })
}

$(document).ready(function () {
    $.get("weather.php", function (data) {
        $("#weather_forecast").html(data)
    });
    setButtonWeatherClick();
    loadData(true);
})

//Обновление прогноза погоды 1 раз в час
$(document).everyTime("3600s", function () {
    $.get("weather.php", function (data) {
        $("#weather_forecast").html(data)
    });
});

//Обновление показания погоды каждые 10 минут
$(document).everyTime("600s", function () {
    updateDataAll();
});
