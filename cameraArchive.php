<?php
$numCamera = $_REQUEST['cam'];
?>

<script src="js2/cameraArchive.js"></script>

<div class="omega ui-corner-all ui-state-default" style="display: flex; flex-direction: column; height: 100%">
    <div id="cam_archive_navigation" style="align-self: flex-start">
        <button id="camera_navigation_timelapse" class="camera_button_nav1" cam="<?php echo $numCamera ?>">Timelapse</button>
        <button id="camera_navigation_video" class="camera_button_nav1" cam="<?php echo $numCamera ?>">Видео</button>
        <button id="camera_navigation_image" class="camera_button_nav1" cam="<?php echo $numCamera ?>">Изображения</button>
<!--        <button id="camera_navigation_back" class="camera_button_nav1">Назад</button>-->
    </div>
    <div id="cam_archive_data" style="align-self: stretch; flex-grow: 1; display: flex; flex-direction: column; height: 100%; margin: 5px">
    </div>
</div>
