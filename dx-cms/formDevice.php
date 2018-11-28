<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.11.18
 * Time: 22:52
 */

if (!isset($_REQUEST['operationDevices'])) {
    echo "<span style='color:red;'>Ошибка получения операции</span>";
    exit;
}

$op = $_REQUEST['operationDevices'];

echo "<span style='color:red;'>Проверка</span>";


?>