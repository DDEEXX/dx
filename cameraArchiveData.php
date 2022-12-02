<?php
include_once(dirname(__FILE__) . '/class/cameras.class.php');

$numCamera = $_GET['cam'];
$dev = $_GET['dev'];
$dataType = empty($_GET['type']) ? null : $_GET['type'];
$path = empty($_GET['path']) ? null : $_GET['path'];

$cam = managerCameras::getCamera($numCamera);

function getPartsDataFromPath($_path) {
    return explode('/', $_path);
}

if (is_null($cam)) {
    exit();
}

echo '<script src="js2/cameraArchiveData.js"></script>';

if ($dev == 'image') {

    if ($dataType == 'year_month' || $dataType == 'day') {

        echo '
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
        echo '<div style="display: grid; grid-template-columns: repeat(4, max-content); grid-gap: 1px">';
        foreach ($imageShots as $nameShot) {
            echo '<div> 
              <img src=cameraArchiveShot.php?cam='.$numCamera.'&path='.$path.'/'.$nameShot .' alt="img2/frame.png" style="width:280px;height:159px">
              </div>';

        }
        echo '</div>';
    }
}
elseif ($dev == 'timelapse') {
    if ($dataType == 'list') {
        $timeLapses = $cam->getArchiveTimelapse();
        echo '<div id="cam_archive_timelapse" 
            style="align-self: stretch; flex-grow: 1; width: 100%; height: 1px; overflow: auto; margin-top: 5px; margin-left: 5px">';
        echo '<table>';
        echo '<thead><tr><th>Дата</th><th>Имя файла</th></tr></thead>';
        echo '<tbody>';

        foreach ($timeLapses as $nameShot) {
            if (preg_match('/^[0-9]{8}-timelapse.avi/', $nameShot)) {
                $dateFile = substr($nameShot, 6, 2) . '.'
                    . substr($nameShot, 4, 2) . '.' .
                    substr($nameShot, 0, 4);
                echo '<tr><th>' . $dateFile . '</th><th cam="' . $numCamera . '">' . $nameShot . '</th></tr>';
            }

        }
        echo '</tbody></table>';
        echo '</div>';
    }
    elseif ($dataType == 'video') {
        $qq = 12;
        ?>

<!--        <div id="cam_video_player"></div>-->
<!---->
<!--        <div class="jp-gui">-->
<!--            <div class="jp-video-play">-->
<!--                <button class="jp-video-play-icon" role="button" tabindex="0">play</button>-->
<!--            </div>-->
<!--            <div class="jp-interface">-->
<!--                <div class="jp-progress">-->
<!--                    <div class="jp-seek-bar">-->
<!--                        <div class="jp-play-bar"></div>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>-->
<!--                <div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>-->
<!--                <div class="jp-controls-holder">-->
<!--                    <div class="jp-controls">-->
<!--                        <button class="jp-play" role="button" tabindex="0">play</button>-->
<!--                        <button class="jp-stop" role="button" tabindex="0">stop</button>-->
<!--                    </div>-->
<!--                    <div class="jp-volume-controls">-->
<!--                        <button class="jp-mute" role="button" tabindex="0">mute</button>-->
<!--                        <button class="jp-volume-max" role="button" tabindex="0">max volume</button>-->
<!--                        <div class="jp-volume-bar">-->
<!--                            <div class="jp-volume-bar-value"></div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="jp-toggles">-->
<!--                        <button class="jp-repeat" role="button" tabindex="0">repeat</button>-->
<!--                        <button class="jp-full-screen" role="button" tabindex="0">full screen</button>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="jp-details">-->
<!--                    <div class="jp-title" aria-label="title">&nbsp;</div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="jp-no-solution">-->
<!--            <span>Update Required</span>-->
<!--            To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.-->
<!--        </div>-->

                <script type="text/javascript" src="js2/jwplayer/jwplayer.js"></script>
                <div id="myElement">Загрузка плеера...</div>

                    <script type="text/javascript">
                    jwplayer("myElement").setup({
                        //file: "123.mp4",
                        file: "333.mp4",
                        //file:"https://www.youtube.com/watch?v=y2lsAPFSNWU",
                    image: "img2/frame.png",
                    width: 960,
                    height: 544,
                    title: "Мой мегаклип",
                    });
                    </script>
<?php
    }

}

?>


