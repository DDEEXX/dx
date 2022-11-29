$(function (){
    $(".camera_button_nav1").button();
    $("#camera_navigation_image").click(function () { //кнопка Image панели управления архива
        var cam = $(this).attr('cam');
        var url = "showCameraArchiveData.php?cam="+cam+"&dev=image";
        $.get(url, function (data) {
            $("#cam_data_archive").html(data);
        });
    })

})
