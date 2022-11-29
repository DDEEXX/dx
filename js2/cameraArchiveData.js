$(function () {
    $(".camera_nav_image_path, .camera_nav_image_year, .camera_nav_image_month, .camera_nav_image_day").button();

    $(".camera_nav_image_month").click(function () { //кнопка месяца
        var cam = $(this).attr('cam');
        var month = $(this).attr('month');
        var url = "showCameraArchiveData.php?cam="+cam+"&dev=image&month="+month;
        $.get(url, function (data) {
            $("#cam_data_archive").html(data);
        });
    })

    $(".camera_nav_image_day").click(function () { //кнопка день
        var cam = $(this).attr('cam');
        var month = $(this).attr('month');
        var day = $(this).attr('day');
        var url = "showCameraArchiveData.php?cam="+cam+"&dev=image&month="+month+"&day="+day;
        $.get(url, function (data) {
            $("#cam_data_archive").html(data);
        });
    })

})
