$(function (){
    $(".camera_button_nav1").button();
    $("#camera_navigation_image").click(function () { //кнопка Image панели управления архива
        var cam = $(this).attr('cam');
        var url = "cameraArchiveData.php?cam="+cam+"&dev=image&type=month";
        $.get(url, function (data) {
            $("#cam_data_archive").html(data);
        });
    })

})
