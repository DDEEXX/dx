<?php

require_once(dirname(__FILE__) . '/class/logger.class.php');

if ($_REQUEST['type'] == "logDefault") { //получаем температру
    $arrLog = logger::readLog(loggerName::DEFAULTLOGGER);
}
if ($_REQUEST['type'] == "logError") { //получаем температру
    $arrLog = logger::readLog(loggerName::ERROR);
}

for ($i=0; $i<count($arrLog); $i++) {
    echo '<p>'.$arrLog[$i]['date'].' '.$arrLog[$i]['type'].' '.$arrLog[$i]['message'].'</p>';
}
