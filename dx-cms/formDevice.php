<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.11.18
 * Time: 22:52
 */

if (!isset($_REQUEST['Operation'])) {
    echo "<span style='color:red;'>������ ��������� ������</span>";
    exit;
}

$op = $_REQUEST['Operation'];

echo "<span style='color:red;'>��������</span>";


?>