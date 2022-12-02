<?php
include_once(dirname(__FILE__) . '/class/cameras.class.php');

$numCamera = $_REQUEST['cam'];
$dev = $_REQUEST['dev'];
$dataType = $_REQUEST['type'];
$path = empty($_REQUEST['path']) ? null : $_REQUEST['path'];

$cam = managerCameras::getCamera($numCamera);

function getPartsDataFromPath($_path) {
    return explode('/', $_path);
}

if (is_null($cam)) {
    exit();
}

?>

<?php
if ($dev === 'image') {

    if ($dataType == 'year_month' || $dataType == 'day') {

        echo '
        <script src="js2/cameraArchiveData.js"></script>
        <div id="cam_archive_path" style="width: 100%; align-self: flex-start">
            <div style="margin-top:5px">
                <button class="camera_nav_image_path">/</button>
                <button class="camera_nav_image_path" cam="<?php echo $numCamera ?>">Image</button>';

                if ($dataType == 'day') {
                    $partsData = getPartsDataFromPath($path);
                    echo '<button class="camera_nav_image_path">' . $partsData[0] . '</button>';
                    $nameMonth = iCamera::MONTH[(int)$partsData[1]];
                    echo '<button class="camera_nav_image_path">' . $nameMonth . '</button>';
                }
            echo '    
            </div>';

        if ($dataType == 'year_month') { //режим вывода структуры архива в виде годов и месяцев
            $dirStructure = $cam->getArchiveImageDirStructureYearMonth();
            foreach ($dirStructure as $curYear => $curMonths) {

                echo '<div class="camera_block_image_year_month">
                      <button class="camera_nav_image_year">' . $curYear . '</button>
                      <span style="display: flex; margin-top: 5px;margin-left: 15px">';

                foreach ($curMonths as $curMonth) {
                    $nameMonth = iCamera::MONTH[(int)$curMonth];
                    if (!is_null($nameMonth)) {
                        echo '<button class="camera_nav_image_month ui-corner-all ui-widget ui-widget-header" cam="' .
                            $numCamera . '" path="'.$curYear.'/'.$curMonth.'">'.$nameMonth.'</button>';
                    }
                }

                echo '</span>
                      </div>';

            }
        } else { //режим вывода дней месяца, выводим все дни
            $partsData = getPartsDataFromPath($path);
            $curYear = $partsData[0];
            $curMonth = $partsData[1];
            $days = $cam->getArchiveImageDays($curYear, $curMonth);
            echo '<div style="display: flex; margin-top: 5px;margin-left: 15px; width: 1140px; flex-wrap: wrap;">';
            foreach ($days as $curDay) {
                echo '<button class="camera_nav_image_day ui-corner-all ui-widget ui-widget-header" cam="' .
                        $numCamera.'" path="'.$path.'/'.$curDay.'">'.$curDay.'</button>';
            }
            echo '</div>';
        }

        echo '
        </div>
        <div id="cam_archive_shots" style="align-self: stretch; flex-grow: 1; width: 100%; height: 1px; overflow: auto">
        </div>';

    } elseif ($dataType == 'shots') {
        $partsData = getPartsDataFromPath($path);
        $curYear = $partsData[0];
        $curMonth = $partsData[1];
        $curDay = $partsData[2];
        $imageShots = $cam->getArchiveImageShots($curYear, $curMonth, $curDay);
        echo '<div style="display: grid; grid-template-rows: 1fr; grid-template-columns: 1fr 1fr 1fr 1fr;">';
        foreach ($imageShots as $nameShot) {
            echo '<div> 
              <img src=cameraArchiveShot.php?cam='.$numCamera.'&path='.$path.'/'.$nameShot .' style="width:280px;height:159px">
              </div>';

        }
        echo '</div>';
    }
}
?>


