<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 28.11.18
 * Time: 11:01
 */

//Ес//ли это изменение данных, то получаем все значения из текщего объекта
if ($_REQUEST['pDevices'] == "updateDevice") {
    try {
        $device = managerDevices::getDevice($_REQUEST['ID']);
        $deviceID   = $device->getDeviceID();
        $adress     = $device->getAdress();
        $netTitle   = $device->getNet();
        $deviceType = $device->getType();
        $disabled   = $device->getDisabled();
        $alarm      = $device->getAlarm();
    }
    catch (managerException $e) {
        $err = "<span style='color:red;'>".$e->getErrorInfoHTML()."</span>";
        echo $err;
        die();
    }
}
?>

<script>
    $(function() {
        $( ".btAdd" ).button();
        $( ".btUpdate" ).button();
        $( ".btCancel" ).button();
    });

    $(document).ready(function () {
        $('#deviceNet').change(function () {
            if ($(this).val() === "1") {
                $(".set_alarm").css("display", "block");
            }
            else {
                $(".set_alarm").css("display", "none");
            }
        });

        $('#deviceNet').change();
    });

</script>

<form action="index.php#tabsDevices" method="post">
    <table>
        <input id='deviceID' type='hidden' name='ID' value='-1'>
        <tr>
            <td><label for='deviceDisabled'>Отключен</label></td>
            <td><input id='deviceDisabled' type='checkbox' name="Disabled"></td>
        </tr>
        <tr>
            <td><label for='deviceType'>Тип</label></td>
            <td>
                <select name='deviceType'>
                    <option selected value = '0'>не выбран</option>
                    <option value = '1'>Датчик температуры</option>
                    <option value = '2'>Метка</option>
                    <option value = '3'>Силовой ключ</option>
                    <option value = '4'>Входящий ключ</option>
                    <option value = '5'>Выходной ключ</option>
                    <option value = '6'>Датчик наличия напряжения</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for='deviceNet'>Соединение</label></td>
            <td>
                <select name='deviceNet' id="deviceNet">
                    <option selected value = '0'>не выбран</option>
                    <option value = '1'>1-wire</option>
                    <option value = '2'>Ethernet</option>
                    <option value = '3'>Cubieboard GPIO</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for='deviceAdress'>Адрес</label></td>
            <td><input id='deviceAdress' type='text' name="Adress"></td>
        </tr>
        <tr>
            <td><label class="set_alarm" for='alarm'>set_alarm</label></td>
            <td><input class="set_alarm" id='alarm' type='text' name="setAlarm"></td>
        </tr>
    </table>

    <input type='hidden' name='pDevices' value='updateDataDevice' >
    <input class='btAdd' type='submit' value='OK' name='btAdd'>
    <input class='btCancel' type='button' onclick='history.back();' value='Cancel'>
</form>

<script>


</script>
