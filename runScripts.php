<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:45
 */

sleep(15);

$arr_s = array('scripts/move_holl.php');

$loop = true;

while ($loop) {

//	for ($i=0; $i<count($arr_s); $i++) {
//		include(dirname(__FILE__).'/'.$arr_s[$i]);
//    }

//    include(dirname(__FILE__).'/'.$arr_s[$i]);
    include('scripts/move_holl.php');

}

?>
