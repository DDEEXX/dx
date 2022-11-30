
$(document).ready(function () {

    $("#cam_archive1").click(function () { //кнопка Архив под изображением с камеры
        $.get("cameraArchive.php?cam=1", function (data) {
            $("#cam_data").html(data);
        });
    });

})

$(function (){
    $(".cam_button").button();
})
