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
    <form action="index.php#tabsDevices" method="get">
        <!--Отправляем на сервер команду по нажатии кнопки "Добавить"-->
        <input name="pDevices" type="hidden" value="addDevice">
        <input class='btAddDevice' type="submit" value="Добавить">
    </form>
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

foreach($listDevices as $key => $device) {

    $deviceID = $device->getDeviceID();
    $address = $device->getAddress();
    $netTitle = $device->getNet();
    $deviceType = $device->getType();

    echo '<tr>';
    echo '<td><a class="btEditDevice" href="index.php?pDevices=updateDevice&ID='.$deviceID.'#tabsDevices"></a></td>';
    echo '<td><a class="btDeleteDevice" href="index.php?pDevices=deleteDevice&ID='.$deviceID.'"></a></td>';
    echo '<td><img src="img2/netDevice_'.$netTitle.'.png"></td>';
    echo '<td>'.$address.'</td>';
    echo '<td><img src="img2/deviceType_'.$deviceType.'.png"></td>';
    echo '</tr>';

}

unset($listDevices); //!!! наверное надо освобождать каждый объект в массиае а не массив целиком

?>

</tbody>
</table>


