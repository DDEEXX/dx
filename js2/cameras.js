
$(document).ready(function () {

    $("#cam_archive1").click(function () {
        $.get("showCameraArchive.php?cam=1&dev=image", function (data) {
            $("#cam_data").html(data);
        });
    });

})

$(function (){
    $(".cam_button").button();
})