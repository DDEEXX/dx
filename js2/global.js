/* песочные часы */
function getWaitElement() {
    return $('<span class="weather_widget_wait"></span>');
}

function _updateSensorsData(selectorClass, urlJson) {

    $('.'+selectorClass).each(function () {
        const url = urlJson + $(this).attr('id') + '.json';
        $.getJSON(url, function (data) {
            const sensorData = data.data;
            const selector = '#' + sensorData.id;
            const value = data.data.value;
            const sensorDigit = $(selector).children("." + value["classDigit"]);
            if (sensorDigit.length>0) {
                /* dev - тип событие, label - имя датчика в базе */
                $.get("modules/getData.php?dev=" + value["dev"] + "&label=" + value["label"], function (data) {
                    $(sensorDigit[0]).html(data);
                });
            }
        });
    });
}

function _clickOnWidget(event) {
    const selector = event.data.selector;
    $(selector).off('click').css('cursor', '');
    $(selector).children(':visible').each(function () {
        $(this).detach();
    });
    $(selector).children(':hidden').each(function () {
        $(this).show();
    });
}

function _getWidgetSensor(widgetData) {
    const selector = '#' + widgetData.id;
    const clear = widgetData.clear;

    $(selector).off('click').css('cursor', '');

    //скрываем/удаляем все элементы и добавляем иконку "ready"
    $(selector).children().each(function () {
        if (clear) {
            $(this).detach();
        } else {
            $(this).hide();
        }
    });
    $(selector).append(getWaitElement());

    const widgetParam = widgetData.properties;
    $.post("modules/graphWidget.php", widgetParam, function (data) {
        //удаляем иконку "ready"
        $(selector).children(":visible").each(function () {
            $(this).detach();
        });
        //вставляем график
        $(selector).append(data).on('click', null, {selector: selector}, _clickOnWidget).css('cursor', 'pointer');
    }, "html");
}

function _clickOnDataSensor(event) {
    const widgetData = event.data;
    _getWidgetSensor(widgetData);
}

function _setClickEventSensorData(selector, widget) {
    $(selector).off('click').on('click', null, widget, _clickOnDataSensor).css('cursor', 'pointer');
}

function _loadSensorHtmlData(data, classData) {
    const sensorData = data.data;
    const selector = '#' + sensorData.id;
    $(selector).load(sensorData.html, function (response, status) {
        if (status === 'success') {
            const value = sensorData.value;
            const sensorDigit = $(selector).find("." + value["classDigit"]);
            sensorDigit.addClass(classData);
            /* dev - тип событие, label - имя датчика в базе */
            $.get("modules/getData.php?dev=" + value["dev"] + "&label=" + value["label"], function (data) {
                $(sensorDigit).html(data);
            });
            if (sensorData.expand) {
                let selectorClick = selector;
                if (sensorData.idClick) {
                    selectorClick = '#'+sensorData.idClick;
                }
                _setClickEventSensorData(selectorClick, data.widget);
            }
        }
    });
}

function _getSensorProperties(url, classData = "") {
    $.getJSON(url, function (data) {
        _loadSensorHtmlData(data, classData);
    });
}
