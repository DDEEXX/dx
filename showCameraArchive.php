<?php

$numCamera = $_REQUEST['cam'];
$dev = $_REQUEST['dev'];
?>

<script>
    $(function (){
        $(".camera_button_nav1").button();
        $("#camera_navigation_image").click(function () {
            $.get("showCameraArchive.php?cam=1&dev=image", function (data) {
                $("#cam_data").html(data);
            });
        })

    })
</script>

<div class="grid_12 alpha omega">
    <div class="ui-corner-all ui-state-default" style="margin-top:5px;height: 747px">
        <button class="camera_button_nav1">Timelaps</button>
        <button class="camera_button_nav1">Video</button>
        <button id="camera_navigation_image" class="camera_button_nav1">Image</button>
        <button id="camera_navigation_image" class="camera_button_nav1">Back</button>
    </div>
</div>
