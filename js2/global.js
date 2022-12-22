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
        // $.get("graphWidget.php", widgetParam, function (data) {
        //     $(selector).html(data);
        // });
        $.post("graphWidget.php", widgetParam, function (data) {
            $(selector).html(data);
        }, "html");
    })
}

/**
 * NEW
 * */

function _clickOnWidget(event) {
    var selector = event.data.selector;
    $(selector).off('click');
    $(selector).children().each(function () {
        var currentEl = $(this);
        if (currentEl.is(":visible")) {
            currentEl.detach();
        }
    });
    $(selector).children().each(function () {
        var currentEl = $(this);
        if (currentEl.is(":hidden")) {
            currentEl.show();
        }
    });
}

function _getWidgetSensor(widgetData) {
    var selector = '#' + widgetData.id;

    var clear = widgetData.clear;
    //скрываем/удаляем все элементы и добавляем иконку "ready"
    $(selector).children().each(function () {
        if (clear) {
            $(this).detach();
        } else {
            $(this).hide();
        }
    });
    $(selector).append(getWaitElement());

    $(selector).off('click');
    var widgetParam = widgetData.properties;
    $.post("graphWidget.php", widgetParam, function (data) {
        //удаляем иконку "ready"
        $(selector).children().each(function () {
            var currentEl = $(this);
            if (currentEl.is(":visible")) {
                currentEl.detach();
            }
        });
        //вставляем график
        $(selector).append(data);
        $(selector).on('click', null, {selector: selector}, _clickOnWidget);
    }, "html");
}

function _clickOnDataSensor(event) {
    var widgetData = event.data;
    _getWidgetSensor(widgetData);
}

function _setClickEventSensorData(selector, widget) {
    $(selector).off('click');
    $(selector).on('click', null, widget, _clickOnDataSensor);
}

function _loadSensorHtmlData(data, classData) {
    var sensorData = data.data;
    var selector = '#' + sensorData.id;
    $(selector).load(sensorData.html, function (response, status) {
        if (status === 'success') {
            var value = sensorData.value;
            var sensorDigit = $(selector).find("." + value["classDigit"]);
            sensorDigit.addClass(classData);
            /* dev - тип событие, label - имя датчика в базе */
            $.get("getData.php?dev=" + value["dev"] + "&label=" + value["label"], function (data) {
                $(sensorDigit).html(data);
            });
            if (sensorData.expand) {
                _setClickEventSensorData(selector, data.widget);
            }
        }
    });
}

function _getSensorProperties(url, classData) {
    $.getJSON(url, function (data) {
        _loadSensorHtmlData(data, classData);
    });
}
