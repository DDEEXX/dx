<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 28.11.18
 * Time: 11:01
 */

$isUpdate = $_REQUEST['pDevices'] == 'updateDevice';

//Если это изменение данных, то получаем все значения из текщего объекта
if ($isUpdate) {
    try {
        $device = managerDevices::getDevice($_REQUEST['ID']);
        $deviceID   = $device->getDeviceID();
        $adress     = $device->getAdress();
        $deviceNet   = $device->getNet();
        $deviceType = $device->getType();
        $disabled   = $device->getDisabled();
        $alarm      = $device->getAlarm();
    }
    catch (managerException $e) {
        $err = '<span style="color:red;">'.$e->getErrorInfoHTML().'</span>';
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

        <?php
        if ($isUpdate) {
            echo 'document.getElementById("deviceType").value = "'.$deviceType.'";';
            echo 'document.getElementById("deviceNet").value = "'.$deviceNet.'";';
        }
        else {
            echo 'document.getElementById("deviceType").value = "0";';
            echo 'document.getElementById("deviceNet").value = "0";';
        }
        ?>

        $('#deviceNet').change(function () {
            if ($(this).val() === "1") {
                $(".set_alarm").css("display", "block");
            }
            else {
                $(".set_alarm").css("display", "none");
            }
        });

        $('#deviceNet').change()

    });

</script>

<form action="index.php#tabsDevices" method="post">
    <table>
        <?php
        if ($isUpdate) {
            echo '<input id="deviceID" type="hidden" name="ID" value="'.$deviceID.'">';
        }
        else {
            echo '<input id="deviceID" type="hidden" name="ID" value="-1">';
        }
        ?>
        <tr>
            <td><label for='deviceDisabled'>Отключен</label></td>
            <td>
                <?php
                if ($isUpdate) {
                    if ($disabled) {
                        echo '<input id="deviceDisabled" type="checkbox" name="Disabled" checked>';
                    }
                    else {
                        echo '<input id="deviceDisabled" type="checkbox" name="Disabled">';
                    }
                }
                else {
                    echo '<input id="deviceDisabled" type="checkbox" name="Disabled">';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><label for='deviceType'>Тип</label></td>
            <td>
                <select name="deviceType" id="deviceType">
                    <option value = '0'>не выбран</option>
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
                <select name="deviceNet" id="deviceNet">
                    <option selected value = '0'>не выбран</option>
                    <option value = '1'>1-wire</option>
                    <option value = '2'>Ethernet</option>
                    <option value = '3'>Cubieboard GPIO</option>
                    <option value = '4'>I2C</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for='deviceAdress'>Адрес</label></td>
            <td>
                <?php
                if ($isUpdate) {
                    echo '<input id="deviceAdress" type="text" name="Adress" value="'.$adress.'">';
                }
                else {
                    echo '<input id="deviceAdress" type="text" name="Adress">';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><label class="set_alarm" for='alarm'>set_alarm</label></td>
            <td>
                <?php
                if ($isUpdate) {
                    echo '<input class="set_alarm" id="alarm" type="text" name="setAlarm" value="'.$alarm.'">';
                }
                else {
                    echo '<input class="set_alarm" id="alarm" type="text" name="setAlarm">';
                }
                ?>
            </td>
        </tr>
    </table>

    <input type='hidden' name='pDevices' value='updateDataDevice' >
    <?php
    if ($isUpdate) {
        echo '<input class="btUpdate" type="submit" value="Сохранить" name="btUpdate">';
    }
    else {
        echo '<input class="btAdd" type="submit" value="Создать" name="btAdd">';
    }
    ?>
    <input class='btCancel' type='button' onclick='history.back();' value='Cancel'>
</form>

<script>


</script>
