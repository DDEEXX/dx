/* песочные часы */
function getWaitElement() {
    return $('<span class="weather_widget_wait"></span>');
}

/*
Вывести числовые данные датчиков, параметры для отображения показаний из json файла
 url - имя json файла с параметрами датчиков
*/
function getDataSensor(url) {
    $.getJSON(url, function (data) {
        data.data.forEach(function (item) {
            /* dev - тип событие */
            /* label - имя датчика в базе */
            $.get("getData.php?dev=" + item["dev"] + "&label=" + item["label"], function (data) {
                $("#" + item["id"]).html(data);
            });
        });
    });
}

/*
 Вывести виджет датчика
*/
function getWidgetSensor(idBlock, idBlockClick, url) {
    var selector = '#' + idBlock;
    $(selector).html(getWaitElement());
    $.getJSON(url, function (data) {
        var widgetParam = data['widget'][idBlockClick];
        $.get("graphWidget.php", widgetParam, function (data) {
            $(selector).html(data);
        });
    })
}
