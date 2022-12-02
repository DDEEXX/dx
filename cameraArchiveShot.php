<?php
include_once(dirname(__FILE__) . '/class/cameras.class.php');

$numCamera = $_REQUEST['cam'];
$path = empty($_REQUEST['path']) ? null : $_REQUEST['path'];

$cam = managerCameras::getCamera($numCamera);

header('Content-Type: image/jpeg');
header('Cache-Control: max-age=86400');
header('Pragma: cache');
header('Expires: ' . date(DATE_RFC2822, time() + 86400));

$fileLocation = $cam->getArchiveImageShotFullFileName($path);
$image = imagecreatefromstring($fileLocation);
$fileHeader = fopen($fileLocation, 'r', false);
$response = '';  //тут можно отдать картинку заглушку, если картинка не найдена по запросу.
if ($fileHeader) {
    $response = stream_get_contents($fileHeader);
    fclose($fileHeader);
}
exit($response);
