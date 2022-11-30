<?php
include_once(dirname(__FILE__) . '/class/cameras.class.php');

$numCamera = $_REQUEST['cam'];
$dev = $_REQUEST['dev'];
$dataType = $_REQUEST['type'];
$month = empty($_REQUEST['month']) ? null : $_REQUEST['month'];
$day = empty($_REQUEST['day']) ? null : $_REQUEST['day'];

$cam = managerCameras::getCamera($numCamera);

function showImageForDay($day) {

    echo $day;

}

if (is_null($cam)) {
    exit();
}

?>

<?php
if ($dev === 'image') { ?>

    <script src="js2/cameraArchiveData.js"></script>

    <div style="margin-top:5px; margin-left: 5px">
        <button class="camera_nav_image_path">/</button>
        <button class="camera_nav_image_path" cam="<?php echo $numCamera ?>">Image</button>

        <?php
        if (!empty($month)) {
            $yearMonth = explode('/', $month);
            echo '<button class="camera_nav_image_path">' . $yearMonth[0] . '</button>';
            $nameMonth = iCamera::MONTH[(int)$yearMonth[1]];
            echo '<button class="camera_nav_image_path">' . $nameMonth . '</button>';
        }
        ?>
    </div>

<?php
    if ($dataType == 'month') { //режим вывода структуры архива в виде годов и месяцев
        $dirStructure = $cam->getImageDirStructureYearMonth();
        foreach ($dirStructure as $curYear => $curMonths) {

            echo '<div class="camera_block_image_year_month">
                  <button class="camera_nav_image_year">' . $curYear . '</button>
                  <span style="display: flex; margin-top: 5px;margin-left: 15px">';

            foreach ($curMonths as $curMonth) {
                $nameMonth = iCamera::MONTH[(int)$curMonth];
                if (!is_null($nameMonth)) {
                    echo '<button class="camera_nav_image_month ui-corner-all ui-widget ui-widget-header" cam="' .
                        $numCamera . '" month ="' . $curYear . '/' . $curMonth . '">' . $nameMonth . '</button>';
                }
            }

            echo '</span>
                  </div>';

        }
    } else {
        if (empty($day)) { //день не задан, выводим все дни
            $yearMonth = explode('/', $month);
            $curYear = $yearMonth[0];
            $curMonth = $yearMonth[1];
            $days = $cam->getImageDays($curYear, $curMonth);
            echo '<div style="display: flex; margin-top: 5px;margin-left: 15px; width: 1140px; flex-wrap: wrap;">';
            foreach ($days as $curDay) {
                echo '<button class="camera_nav_image_day ui-corner-all ui-widget ui-widget-header" cam="' .
                    $numCamera . '" month ="' . $curYear . '/' . $curMonth . '" day="'.$curDay.'">' . $curDay . '</button>';
            }
            echo '</div>';
        }
        else {
            showImageForDay($day);
        }
    }
}


