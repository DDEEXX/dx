/* песочные часы */
function getWaitElement() {
    return $('<span class="weather_widget_wait"></span>');
}

function _clickOnWidget(event) {
    var selector = event.data.selector;
    $(selector).off('click').css('cursor', '');
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

    $(selector).off('click').css('cursor', '');
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
        $(selector).append(data).on('click', null, {selector: selector}, _clickOnWidget).css('cursor', 'pointer');
    }, "html");
}

function _clickOnDataSensor(event) {
    var widgetData = event.data;
    _getWidgetSensor(widgetData);
}

function _setClickEventSensorData(selector, widget) {
    $(selector).off('click').on('click', null, widget, _clickOnDataSensor).css('cursor', 'pointer');
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
