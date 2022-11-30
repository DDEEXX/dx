<?php
$numCamera = $_REQUEST['cam'];
?>

<script src="js2/cameraArchive.js"></script>

<div class="grid_12 alpha omega">
    <div class="ui-corner-all ui-state-default" style="margin-top:5px;height: 747px">
        <div>
            <button class="camera_button_nav1">Timelapse</button>
            <button class="camera_button_nav1">Video</button>
            <button id="camera_navigation_image" class="camera_button_nav1" cam="<?php echo $numCamera ?>">Image</button>
            <button id="camera_navigation_back" class="camera_button_nav1">Back</button>
        </div>
        <div id="cam_data_archive"></div>
    </div>
</div>
