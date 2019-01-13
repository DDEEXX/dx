<?php
require_once("class/sqlDataBase.class.php");
require_once("class/device.class.php");
require_once("class/logger.class.php");
require_once("class/managerDevices.class.php");
require_once("class/lists.class.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">

<head>
	<title>без имени</title>
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

$sel = new selectOption();
$sel->set('SensorTypeID',1);

$arr = DB::getListUnits($sel);

var_dump($arr);


?>

</body>

</html>
