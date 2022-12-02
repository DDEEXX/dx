$(document).ready(function(){

    $("#cam_video_player").jPlayer({
        ready: function () {
            $(this).jPlayer("setMedia", {
                title: "dx home",
                m4v: "333.mp4",
                poster: "img2/frame.png"
            });
        },
        swfPath: "js2/jPlayer/dist/jplayer",
        supplied: "webmv, ogv, m4v",
        size: {
            width: "640px",
            height: "360px",
            cssClass: "jp-video-360p"
        },
        useStateClassSkin: true,
        autoBlur: false,
        smoothPlayBar: true,
        keyEnabled: true,
        remainingDuration: true,
        toggleDuration: true
    });

});

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
        var element = $(this).children("th").eq(1);
        var nameFile = element.html();
        var cam = element.attr("cam");
        var url = "cameraArchiveData.php?cam="+cam+"&dev=timelapse&type=video&path="+nameFile;
        $.get(url, function (data) {
            $("#cam_archive_timelapse").html(data);
        });
    });

})