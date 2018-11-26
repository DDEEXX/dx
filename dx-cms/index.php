<?php
    require_once("../class/managerDevices.class.php");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<?php header('Content-type: text/html; charset=windows-1251')?>
<title>DX HOME CMS</title>
<script src="../js2/jquery.js"></script>
<script src="../js2/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="../css2/reset.css">
<link rel="stylesheet" type="text/css" href="../css2/960_12_col.css">
<link rel="stylesheet" type="text/css" href="css2/vader/jquery-ui.css">
<script>
  $( function() {
    $( "#tabs" ).tabs();
  } );
  </script>
</head>
<body style="background-image: url(../css2/temes/dx/images/background.jpg);">

	<?php

    //Если не задан параметр p установим его
	if ( !isset($_REQUEST['p']) ) {
		$_REQUEST['p'] = "list";
	};

	?>

	<div id="tabs"
		style="width: 980px; margin-left: auto; margin-right: auto;">
		<ul>
            <li><a href="#tabs-1">Датчики</a></li>
            <li><a href="#tabs-2">Модули</a></li>
            <li><a href="#tabs-3">i-Button</a></li>
            <li><a href="#tabs-4">Видео</a></li>
            <li><a href="#tabs-5">Физ. устройства</a></li>
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
			<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus
				gravida ante, ut pharetra massa metus id nunc. Duis scelerisque
				molestie turpis. Sed fringilla, massa eget luctus malesuada, metus
				eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet
				fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam.
				Praesent in eros vestibulum mi adipiscing adipiscing. Morbi
				facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut
				posuere viverra nulla. Aliquam erat volutpat. Pellentesque
				convallis. Maecenas feugiat, tellus pellentesque pretium posuere,
				felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris
				consectetur tortor et purus.</p>
		</div>
		<div id="tabs-3">
			<p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti.
				Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat,
				eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent
				taciti sociosqu ad litora torquent per conubia nostra, per inceptos
				himenaeos. Fusce sodales. Quisque eu urna vel enim commodo
				pellentesque. Praesent eu risus hendrerit ligula tempus pretium.
				Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
			<p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at,
				semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra
				justo vitae neque. Praesent blandit adipiscing velit. Suspendisse
				potenti. Donec mattis, pede vel pharetra blandit, magna ligula
				faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque.
				Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi
				lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean
				vehicula velit eu tellus interdum rutrum. Maecenas commodo.
				Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus
				hendrerit hendrerit.</p>
		</div>
		<div id="tabs-4">
			<p>Видео</p>
		</div>
		<div id="tabs-5">
			<?php
			if ($_REQUEST['p'] == "deleteDevice") {
				//include 'DeleteSensor.php';
			}
			else {
				include 'ListDevices.php';
			}
			?>
		</div>
	</div>


</body>
</html>
