<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11.02.19
 * Time: 22:45
 */

sleep(15);

$arr_s = array('move_holl.php');

$loop = true;

//$j=0;

while ($loop) {

	for ($i=0; $i<count($arr_s); $i++) {
		require (dirname(__FILE__).'scripts/'.$arr_s[$i]);
    }

	//$j++;

//	if ($j>150) {
//		$loop = false;
//	}

}

?>
