$(document).ready(function(){

    $("#cam_video_player").jPlayer({
        ready: function () {
            $(this).jPlayer("setMedia", {
                title: "dx home",
                m4v: "333.mp4",
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
