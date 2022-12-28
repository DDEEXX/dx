$(function (){
    $(".camera_button_nav1").button();
    $("#camera_navigation_timelapse").click(function () { //кнопка Image панели управления архива
        const cam = $(this).attr('cam');
        const url = "cameraArchiveData.php?cam=" + cam + "&dev=timelapse&type=list";
        $.get(url, function (data) {
            $("#cam_archive_data").html(data);
        });
    })
    $("#camera_navigation_video").click(function () { //кнопка Image панели управления архива
        const cam = $(this).attr('cam');
        const url = "cameraArchiveData.php?cam=" + cam + "&dev=video&type=list";
        $.get(url, function (data) {
            $("#cam_archive_data").html(data);
        });
    })
    $("#camera_navigation_image").click(function () { //кнопка Image панели управления архива
        const cam = $(this).attr('cam');
        const url = "cameraArchiveData.php?cam=" + cam + "&dev=image&type=year_month";
        $.get(url, function (data) {
            $("#cam_archive_data").html(data);
        });
    })
})
