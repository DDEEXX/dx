$(document).ready(function () {

    $("#cam_Monitor_1_full_size").html('<img src="https://192.168.1.4/camera_0" alt="https://192.168.1.4/camera_0">');

})

$(function () {
    $(".cam_button").button();

    $("#cam_archive_1").click(function () { //кнопка Архив под изображением с камеры 1
        $.get("data/cam/cameraArchive.php?cam=1", function (data) {
            $("#cam_data").html(data);
        });
    });

    $("#cam_monitor_1").on("click", function () {
        $("#cam_Monitor_1_full_size").dialog("open");
    });

    const cam_monitor_1_dialog = "#cam_Monitor_1_full_size";
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
})
