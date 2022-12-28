$(function () {
    $(".camera_nav_image_path, .camera_nav_image_year, .camera_nav_image_month, .camera_nav_image_day").button();

    $(".camera_nav_image_month").click(function () { //кнопка месяца
        var cam = $(this).attr('cam');
        var path = $(this).attr('path');
        var url = "cameraArchiveData.php?cam="+cam+"&dev=image&type=day&path="+path;
        $.get(url, function (data) {
            $("#cam_archive_data").html(data);
        });
    })

    $(".camera_nav_image_day").click(function () { //кнопка день
        var cam = $(this).attr('cam');
        var path = $(this).attr('path');
        var url = "cameraArchiveData.php?cam="+cam+"&dev=image&type=shots&path="+path;
        $.get(url, function (data) {
            $("#cam_archive_shots").html(data);
        });
    })

    $("#cam_archive_timelapse table tbody tr").click(function () {
        const element = $(this).children("th").eq(1);
        const nameFile = element.html();
        const cam = $(this).attr("cam");
        const url = "cameraArchiveData.php?cam=" + cam + "&dev=timelapse&type=video&path=" + nameFile;
        $.get(url, function (data) {
            $("#cam_archive_timelapse").html(data);
        });
    });

    $("#cam_archive_video table tbody tr").click(function () {
        var element = $(this).children("th").eq(1);
        var nameFile = element.html();
        var cam = $(this).attr("cam");
        var url = "cameraArchiveData.php?cam="+cam+"&dev=video&type=video&path="+nameFile;
        $.get(url, function (data) {
            $("#cam_archive_video").html(data);
        });
    });

})