$(document).ready(function () {

    $("#cam_Monitor_1_full_size").html('<img src="http://192.168.1.4:8081/" alt="http://192.168.1.4:8081/">');

})

$(function () {
    $(".cam_button").button();

    $("#cam_archive_1").click(function () { //кнопка Архив под изображением с камеры 1
        $.get("cameraArchive.php?cam=1", function (data) {
            $("#cam_data").html(data);
        });
    });

    $("#cam_monitor_1").on("click", function () {
        $("#cam_Monitor_1_full_size").dialog("open");
    });

    var cam_monitor_1_dialog = "#cam_Monitor_1_full_size";
    $(cam_monitor_1_dialog).dialog({
        autoOpen: false,
        draggable: false,
        position: {my: "center", at: "center", of: "#page_cameras"},
        resizable: false,
        title: "Камера 1",
        height: "auto",
        width: 962
    });
    $(cam_monitor_1_dialog).on("click", function () {
        $("#cam_Monitor_1_full_size").dialog("close");
    });

    $("#cam_archive_timelapse table tbody tr").click(function () {
        var element = $(this).children("th").eq(1);
        var nameFile = element.html();
        var cam = $(this).attr("cam");
        var url = "cameraArchiveData.php?cam="+cam+"&dev=timelapse&type=video&path="+nameFile;
        $.get(url, function (data) {
            $("#cam_archive_timelapse").html(data);
        });
    });

})
