<?php
$wiredir = "/mnt/1wire/";
$tempdir = "28.FFAAD6711503";

$f = file($wiredir . $tempdir . "/temperature12");
if ($f === false) {
    echo " ERROR \n";
}
else {
    echo $f[0];
}

//$wiredir = "/mnt/1wire/uncached/";
//$alarmdir = $wiredir . "alarm";
//$key = "12.68441B000000";
//$i = 0;
//while ($i<=100) {
//    usleep(100000);
//    if ($handle = opendir($alarmdir)) {
//        while (false !== ($file = readdir($handle))) {
//            if ($file != "." && $file != "..") {
//                if ($file == $key) {
//                    $f = file($wiredir . $file . "/sensed.A");
//                    echo $f[0];
//                    echo " ....ON... \n";
//                } else {
//                    echo " off \n";
//                }
//            }
//        }
//        rewinddir($handle);
//    }
//    $i++;
//}
//closedir($handle);
