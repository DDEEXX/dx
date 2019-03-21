<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:45
 */

//sleep(15);

$arr_s = array('scripts/move_hall.php');

$loop = true;

while ($loop) {

    usleep(1000);

    for ($i = 0; $i < count($arr_s); $i++) {
        $pathScript = dirname(__FILE__) . '/' . $arr_s[$i];
        /** @noinspection PhpIncludeInspection */
        include($pathScript);
    }

}