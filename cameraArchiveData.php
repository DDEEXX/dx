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

if ($dev == 'image') {

    if ($dataType == 'year_month' || $dataType == 'day') {

        echo '<link rel="stylesheet" type="text/css" href="css2/style_camerasShots.css">';
        echo '<script src="js2/cameraArchiveData.js"></script>';

        echo '
        <div id="cam_archive_path" style="width: 100%; align-self: flex-start">
            <div style="margin-top:5px">
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

    }
    elseif ($dataType == 'shots') {

        echo '<script src="js2/cameraArchiveShots.js"></script>';

        $partsData = getPartsDataFromPath($path);
        $curYear = $partsData[0];
        $curMonth = $partsData[1];
        $curDay = $partsData[2];
        $imageShots = $cam->getArchiveImageShots($curYear, $curMonth, $curDay);
        echo '<div style="display: grid; grid-template-columns: repeat(4, max-content); grid-gap: 1px">';
        foreach ($imageShots as $key=>$nameShot) {
            echo '<div> 
              <img src='.$cam->getArchiveImageLocalFileName($curYear, $curMonth, $curDay, $nameShot)
                .' onclick="openModal();currentSlide('.($key+1).')" alt="img2/frame.png" style="width:280px;height:159px">
              </div>';
        }
        echo '</div>';

        echo '<div id="modalShots" class="modalArchiveShot">
                <span class="modalShotsClose" onclick="closeModal()">&times;</span>
                <div class="modalArchiveShotContent">';

        $countShots = count($imageShots);
        foreach ($imageShots as $key=>$nameShot) {
            echo '<div class="archiveShotSlides">
                    <div class="shotNumberText">' .($key+1).' / '.$countShots.'</div>
                    <img src="'.$cam->getArchiveImageLocalFileName($curYear, $curMonth, $curDay, $nameShot).'" style="width: 100%">
                  </div>';
        }

        echo '
                <!-- Next/previous controls -->
                <a class="archiveShotPrev" onclick="plusSlides(-1)">&#10094;</a>
                <a class="archiveShotNext" onclick="plusSlides(1)">&#10095;</a>
    
                <!-- Caption text -->
                <div class="caption-container">
                  <p id="caption"></p>
                </div>';

        echo '<div style="display: flex; flex-wrap : wrap; background: rgb(0, 0, 0) ">';

        $widthSlide = 100/($countShots>0?(100/$countShots):1);
        foreach ($imageShots as $key=>$nameShot) {
            echo '<img class="column demo" src="'.$cam->getArchiveImageLocalFileName($curYear, $curMonth, $curDay, $nameShot)
                .'" onclick="currentSlide('.($key+1).')" alt="Nature">';
        }
        echo '</div>';
        echo '</div>';
    }
}
elseif ($dev == 'timelapse') {
    if ($dataType == 'list') {

        echo '<script src="js2/cameraArchiveData.js"></script>';
        echo '<h2>TIMELAPSE FILES</h2>';

        $timeLapses = $cam->getListArchiveTimelapseFiles();
        echo '<div id="cam_archive_timelapse" 
            style="align-self: stretch; flex-grow: 1; width: 100%; height: 1px; overflow: auto; margin-top: 5px; margin-left: 5px">';
        echo '<table>';
        echo '<thead><tr><th style="min-width: 100px">Дата</th><th style="min-width: 200px">Имя файла</th></tr></thead>';
        echo '<tbody>';

        foreach ($timeLapses as $nameShot) {
            if (preg_match('/^[0-9]{8}-timelapse.mp4/', $nameShot)) {
                $dateFile = substr($nameShot, 6, 2) . '.'
                    . substr($nameShot, 4, 2) . '.' .
                    substr($nameShot, 0, 4);
                echo '<tr cam="' . $numCamera . '"><th>' . $dateFile . '</th><th>' . $nameShot . '</th></tr>';
            }

        }
        echo '</tbody></table>';
        echo '</div>';
    }
    elseif ($dataType == 'video') {
        ?>

        <script type="text/javascript" src="js2/jPlayer/jquery.jplayer.min.js"></script>
        <script type="text/javascript" src="js2/camTimelapse.js"></script>

        <div id="jp_container_1" class="jp-video jp-video-360p" role="application" aria-label="media player">
            <div class="jp-type-single">

                <div id="cam_video_player" tl_name = "<?php echo $cam->getArchiveTimelapseLocalFileName($path)?>"></div>
                <div class="jp-gui">
                    <div class="jp-video-play">
                        <button class="jp-video-play-icon" role="button" tabindex="0">play</button>
                    </div>
                    <div class="jp-interface">
                        <div class="jp-progress">
                            <div class="jp-seek-bar">
                                <div class="jp-play-bar"></div>
                            </div>
                        </div>
                        <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
                        <div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
                        <div class="jp-controls-holder">
                            <div class="jp-controls">
                                <button class="jp-play" role="button" tabindex="0">play</button>
                                <button class="jp-stop" role="button" tabindex="0">stop</button>
                            </div>
                            <div class="jp-volume-controls">
                                <button class="jp-mute" role="button" tabindex="0">mute</button>
                                <button class="jp-volume-max" role="button" tabindex="0">max volume</button>
                                <div class="jp-volume-bar">
                                    <div class="jp-volume-bar-value"></div>
                                </div>
                            </div>
                            <div class="jp-toggles">
                                <button class="jp-repeat" role="button" tabindex="0">repeat</button>
                                <button class="jp-full-screen" role="button" tabindex="0">full screen</button>
                            </div>
                        </div>
                        <div class="jp-details">
                            <div class="jp-title" aria-label="title">&nbsp;</div>
                        </div>
                    </div>
                </div>
                <div class="jp-no-solution">
                    <span>Update Required</span>
                    To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
                </div>

            </div>
        </div>
<?php
    }

}
elseif ($dev == 'video') {
    if ($dataType == 'list') {

        echo '<script src="js2/cameraArchiveData.js"></script>';
        echo '<h2>VIDEO FILES</h2>';

        $timeLapses = $cam->getListArchiveVideoFiles();
        echo '<div id="cam_archive_video" 
            style="align-self: stretch; flex-grow: 1; width: 100%; height: 1px; overflow: auto; margin-top: 5px; margin-left: 5px">';
        echo '<table>';
        echo '<thead><tr><th style="min-width: 100px">Дата</th><th style="min-width: 200px">Имя файла</th></tr></thead>';
        echo '<tbody>';

        foreach ($timeLapses as $nameShot) {
            if (preg_match('/^[0-9]{8}.*\.mp4$/i', $nameShot)) {
                $dateFile = substr($nameShot, 6, 2) . '.'
                    . substr($nameShot, 4, 2) . '.' .
                    substr($nameShot, 0, 4);
                echo '<tr cam="' . $numCamera . '"><th>' . $dateFile . '</th><th>' . $nameShot . '</th></tr>';
            }

        }
        echo '</tbody></table>';
        echo '</div>';
    }
    elseif ($dataType == 'video') {
        ?>

        <script type="text/javascript" src="js2/jPlayer/jquery.jplayer.min.js"></script>
        <script type="text/javascript" src="js2/camTimelapse.js"></script>

        <div id="jp_container_1" class="jp-video jp-video-360p" role="application" aria-label="media player">
            <div class="jp-type-single">

                <div id="cam_video_player" tl_name = "<?php echo $cam->getArchiveVideoLocalFileName($path)?>"></div>
                <div class="jp-gui">
                    <div class="jp-video-play">
                        <button class="jp-video-play-icon" role="button" tabindex="0">play</button>
                    </div>
                    <div class="jp-interface">
                        <div class="jp-progress">
                            <div class="jp-seek-bar">
                                <div class="jp-play-bar"></div>
                            </div>
                        </div>
                        <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
                        <div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
                        <div class="jp-controls-holder">
                            <div class="jp-controls">
                                <button class="jp-play" role="button" tabindex="0">play</button>
                                <button class="jp-stop" role="button" tabindex="0">stop</button>
                            </div>
                            <div class="jp-volume-controls">
                                <button class="jp-mute" role="button" tabindex="0">mute</button>
                                <button class="jp-volume-max" role="button" tabindex="0">max volume</button>
                                <div class="jp-volume-bar">
                                    <div class="jp-volume-bar-value"></div>
                                </div>
                            </div>
                            <div class="jp-toggles">
                                <button class="jp-repeat" role="button" tabindex="0">repeat</button>
                                <button class="jp-full-screen" role="button" tabindex="0">full screen</button>
                            </div>
                        </div>
                        <div class="jp-details">
                            <div class="jp-title" aria-label="title">&nbsp;</div>
                        </div>
                    </div>
                </div>
                <div class="jp-no-solution">
                    <span>Update Required</span>
                    To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
                </div>

            </div>
        </div>
        <?php
    }

}

?>


