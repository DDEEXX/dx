$(document).ready(function () {

    $( ".logger" ).buttonset();

    $(".set_type_log").click(function () {
        $.get("logFile.php?type=" + $(this).attr("id"), function (data) {
            $("#logFile").html(data);
        });
    });

});