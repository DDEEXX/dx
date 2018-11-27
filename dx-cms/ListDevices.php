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
	padding: 1px 5px; /* ���� ������ ������ */
}
</style>

<div style="margin-bottom: 5px">
	<a class='btAddDevice' href='index.php?p=AddNewDevice'>��������</a>
</div>

<table>
<thead class=\"ui-widget-header\">
<tr  style='padding-right: 5px'>
<th></th>
<th></th>
<th>����</th>
<th>�����</th>
<th>���</th>
</tr>
</thead>
<tbody>

<?php

/**�������� ������ ���� ���. ���������*/
$listDevices = managerDevices::getListDevices();

/**
$sel = new selectOption();
$sel->set('netTypeID', netDevice::ONE_WIRE);
//$sel->set('SensorTypeID', typeDevice::TEMPERATURE);
//$sel->set('Disabled', 1);
$listDevices = managerDevices::getListDevices($sel);
unset($sel);
*/

foreach($listDevices as $key => $value) {
    echo "<tr>";

    $deviceID = $value->getDeviceID();
    $adress = $value->getAdress();
    $netTitle = $value->getNet(); //['NetTitle'];
    $deviceType = $value->getType(); //['SensorType'];

    echo "<td><a class='btEditDevice' href='index.php?p=recordDevice&id=$deviceID'></a></td>";
    echo "<td><a class='btDeleteDevice' href='index.php?p=deleteDevice&id=$deviceID'></a></td>";

    echo "<td><img src='img2/netDevice_".$netTitle.".png'></td>";

    echo "<td>".$adress."</td>";

    echo "<td><img src='img2/deviceType_".$deviceType.".png'></td>";

    echo "</tr>";
}

unset($listDevices);

?>

</tbody>
</table>


