$(document).ready(function(){

    var selector_video_player = "#cam_video_player";
    var fileName = $(selector_video_player).attr('tl_name');

    $(selector_video_player).jPlayer({
        ready: function () {
            $(this).jPlayer("setMedia", {
                title: "dx home",
                // m4v: "cam2/archive/cam1/timelapse/20221203-timelapse.mp4",
                m4v: fileName,
                poster: "img2/frame.png"
            });
        },
        // swfPath: "js2/jPlayer",
        supplied: "m4v",
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
