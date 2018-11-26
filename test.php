<?php
require_once("class/sqlDataBase.class.php");
require_once("class/device.class.php");
require_once("class/logger.class.php");
require_once("class/managerDevices.class.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">

<head>
<?php header('Content-type: text/html; charset=windows-1251')?>	
	<title>без имени</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 1.24.1" />
</head>

<body>
<?php

try {
    $con = sqlDataBase::Connect();
}
catch(connectDBException $e) {
	echo $e->getErrorInfoHTML();
	die();
}

$query = "SELECT a.DeviceID, a.Adress, a.set_alarm, b.Title NetTitle, c.Title SensorType
				FROM tdevice a
				LEFT JOIN tnettype b ON a.NetTypeID = b.NetTypeID
				LEFT JOIN tsensortype c ON a.SensorTypeID = c.SensorTypeID";

try {
    $ar = queryDataBase::getOne($con, $query);
    echo var_dump($ar);
}
catch (querySelectDBException $e) {
    echo $e->getErrorInfoHTML();
    die();
}

$am = managerDevices::arrayManagersDevices();

foreach ($am as &$nameManager) {
    $manager = managerDevices::getDeviceManager($nameManager);
    $z = $manager::showType();
    echo $z;
}

?>	

</body>

</html>
