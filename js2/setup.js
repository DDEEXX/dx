

function updateTestStatus() {

    $.post("getData.php", {dev: "test_status"}, function (data) {
        if (data.green) {
            $("#button_test_status #status_test_green").addClass('this_status');
        } else {
            $("#button_test_status #status_test_green").removeClass('this_status');
        }
        if (data.yellow) {
            $("#button_test_status #status_test_yellow").addClass('this_status');
        } else {
            $("#button_test_status #status_test_yellow").removeClass('this_status');
        }
        if (data.red) {
            $("#button_test_status #status_test_red").addClass('this_status');
        } else {
            $("#button_test_status #status_test_red").removeClass('this_status');
        }
    }, "json");
}

$(function () {
    $("#button_test_status").button();
    updateTestStatus();
})

$(document).everyTime("60s", function () {
    updateTestStatus();
});
