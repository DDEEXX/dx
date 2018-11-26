<?php
require_once(dirname(__FILE__)."/../class/globalConst.interface.php");
?>

<script>
  $(function() {
	$( ".btAddDevice" ).button();
    $( ".btEditDevice" ).button({
      icons: {
        primary: "ui-icon-pencil"
      },
      text: false
    });
    $( ".btDeleteDevice" ).button({
        icons: {
          primary: "ui-icon-trash"
        },
        text: false
    });
  });
  </script>

<style>
td, th {
	padding: 1px 5px; /* Поля вокруг текста */
}
</style>

<div style="margin-bottom: 5px">
	<a class='btAddDevice' href='index.php?p=AddNewDevice'>Добавить</a>
</div>

<table>
<thead class=\"ui-widget-header\">
<tr  style='padding-right: 5px'>
<th></th>
<th></th>
<th>Сеть</th>
<th>Адрес</th>
<th>Тип</th>
</tr>
</thead>
<tbody>

<?php

/**Получить список всех физ. устройств*/
$listDevices = managerDevices::getListDevices();

$sel = new selectOption();
$sel->set('netTypeID', netDevice::ONE_WIRE);
//$sel->set('SensorTypeID', typeDevice::TEMPERATURE);
//$sel->set('Disabled', 1);
$listDevices = managerDevices::getListDevices($sel);
unset($sel);

foreach($listDevices as $key => $value) {
    echo "<tr>";

    $DeviceID = $value['DeviceID'];
    $Adress = $value['Adress'];
    $NetTitle = $value['NetTitle'];
    $SensorType = $value['SensorType'];
    if (!isset($SensorType)) {
        $SensorType = 'NA';
    }

    echo "<td><a class='btEditDevice' href='index.php?p=recordDevice&id=$DeviceID'></a></td>";
    echo "<td><a class='btDeleteDevice' href='index.php?p=deleteDevice&id=$DeviceID'></a></td>";

    if (is_null($NetTitle)){
        echo "<td align='center'><img src='../img2/disconnect.png' alt='N/A'></td>";
    }
    elseif ($NetTitle == '1-wire'){
        echo "<td align='center'><img src='../img2/1-wire.png' alt='1-wire'></td>";
    }

    echo "<td>".$Adress."</td>";
    echo "<td><img src='img2/".$SensorType.".png'></td>";

    echo "</tr>";
}

?>

</tbody>
</table>


