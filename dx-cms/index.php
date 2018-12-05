<?php
    require_once("../class/sqlDataBase.class.php");
    require_once("../class/managerDevices.class.php");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<?php header('Content-type: text/html; charset=windows-1251')?>
<title>DX HOME CMS</title>
<script src="../js2/jquery.js"></script>
<script src="../js2/jquery-ui.js"></script>
<!--
<link rel="stylesheet" type="text/css" href="../css2/reset.css">
<link rel="stylesheet" type="text/css" href="../css2/960_12_col.css">
-->
<link rel="stylesheet" type="text/css" href="css2/vader/jquery-ui.css">
<script>
  $(function() {
      $( "#tabs" ).tabs();
    }
  );
</script>
</head>
<body style="background-image: url(../css2/temes/dx/images/background.jpg);">

	<?php

    //Если не задан параметр p установим его
	if ( !isset($_REQUEST['p']) ) {
		$_REQUEST['p'] = "list";
	};
    if ( !isset($_REQUEST['pDevices']) ) {
        $_REQUEST['pDevices'] = "list";
    };

	?>

	<div id="tabs"
		style="width: 980px; margin-left: auto; margin-right: auto;">
		<ul>
            <li><a href="#tabs-1">Датчики</a></li>
            <li><a href="#tabs-2">Модули</a></li>
            <li><a href="#tabs-3">i-Button</a></li>
            <li><a href="#tabs-4">Видео</a></li>
            <li><a href="#tabsDevices">Физ. устройства</a></li>
		</ul>
		<div id="tabs-1">
			<?php
            /**
			if (isset($_REQUEST['btUpdate']) || isset($_REQUEST['btAdd']) || isset($_REQUEST['btDelete'])) {
				include 'UpdateSensor.php';
			}
			elseif (($_REQUEST['p'] == "recordSensor") || ($_REQUEST['p'] == "AddNewUnit")) {
				include 'EditSensor.php';
			}
			elseif ($_REQUEST['p'] == "deleteSensor") {
				include 'DeleteSensor.php';
			}
			else {
				include 'ListSensors.php';
			}
			*/
			?>
		</div>
		<div id="tabs-2">
			<p>111</p>
		</div>
		<div id="tabs-3">
			<p>222</p>
            <?php
            if ($_REQUEST['p'] == 'list')
                echo "LIST";
            else
                echo "NO LIST";
            ?>
		</div>
		<div id="tabs-4">
			<p>Видео</p>
		</div>
		<div id="tabsDevices">
			<?php
            if ($_REQUEST['pDevices'] == "updateDataDevice") {
                include 'updateDevice.php';
            }
			elseif ($_REQUEST['pDevices'] == "addDevice" || $_REQUEST['pDevices'] == "updateDevice") {
				include 'formDevice.php';
			}
			else {
				include 'listDevices.php';
			}
			?>
		</div>
	</div>


</body>
</html>
