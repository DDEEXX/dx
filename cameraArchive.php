<?php
$numCamera = $_REQUEST['cam'];
?>

<script src="js2/cameraArchive.js"></script>

<div class="omega ui-corner-all ui-state-default" style="display: flex; flex-direction: column; height: 100%">
    <div id="cam_archive_navigation">
        <button class="camera_button_nav1">Timelapse</button>
        <button class="camera_button_nav1">Video</button>
        <button id="camera_navigation_image" class="camera_button_nav1" cam="<?php echo $numCamera ?>">Image</button>
        <button id="camera_navigation_back" class="camera_button_nav1">Back</button>
    </div>
    <div id="cam_archive_data" style="align-self: stretch; flex-grow: 1; display: flex; flex-direction: column; height: 100%; margin: 5px">
    </div>
</div>
