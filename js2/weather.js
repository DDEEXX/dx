var arrBlockID = ['block_weather_outdoor',
    'block_weather_hall',
    'block_weather_server',
    'block_weather_bedroom',
    'block_weather_bedroom_Lera',
    'block_weather_bathroom'];

function getSensorWidget(idBlock, data) {
    var selector = '#' + idBlock;
    $(selector).off('click');
    $(selector).html(data);
    $(selector).on('click', function () {
        loadBlockData(idBlock);
    });
}

function clickOnDataSensor() {
    /*параметры виджетов в виде JSON хранятся в файле, имя файла совпадает с id родительского блока с атрибутом isBlock="true"*/
    var idBlock = $(this).parentsUntil("div[isBlock=\"true\"]").parent().attr('id');
    var idBlockClick = $(this).attr('id');
    var url = 'setup/weather/widget/' + idBlock + '.json';
    $.getJSON(url, function (data) {
        var widgetParam = data['widget'][idBlockClick];
        $.get("graphWidget.php", widgetParam, function (data) {
            getSensorWidget(idBlock, data);
        });
    })
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
    var url = 'setup/weather/widget/' + idBlock + '.json';
    $.getJSON(url, updateDataJSON);
}

/** загрузка в блок на странице, данных датчиков (удаляет пред. события на click и назначение новое)
 * @param {string} idBlock
 */
function loadBlockData(idBlock) {
    var selector = "#" + idBlock;
    $(selector).off('click');
    $(selector).load('setup/weather/load/' + idBlock + '.html', idBlock, function (response, status) {
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

$(document).ready(function () {
    $.get("weather.php", function (data) {
        $("#weather_forecast").html(data)
    });
    loadWeatherData();
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
