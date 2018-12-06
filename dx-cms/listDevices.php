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
    <form action="index.php#tabsDevices" method="get">
        <!--���������� �� ������ ������� �� ������� ������ "��������"-->
        <input name="pDevices" type="hidden" value="addDevice">
        <input class='btAddDevice' type="submit" value="��������">
    </form>
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

foreach($listDevices as $key => $device) {

    $deviceID = $device->getDeviceID();
    $adress = $device->getAdress();
    $netTitle = $device->getNet();
    $deviceType = $device->getType();

    echo "<tr>";
    echo "<td><a class='btEditDevice' href='index.php?pDevices=updateDevice&ID=$deviceID#tabsDevices'></a></td>";
    echo "<td><a class='btDeleteDevice' href='index.php?pDevices=deleteDevice&ID=$deviceID'></a></td>";
    echo "<td><img src='img2/netDevice_".$netTitle.".png'></td>";
    echo "<td>".$adress."</td>";
    echo "<td><img src='img2/deviceType_".$deviceType.".png'></td>";
    echo "</tr>";
}

unset($listDevices); //!!! �������� ���� ����������� ������ ������ � ������� � �� ������ �������

?>

</tbody>
</table>


