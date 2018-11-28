<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 28.11.18
 * Time: 11:01
 */

?>

<script>
    $(function() {
        $( ".btAdd" ).button();
        $( ".btCancel" ).button();
    });
</script>

<form action="index.php#tabsDevices" method="post">
    <table>
        <input id='deviceID' type='hidden' name='ID' value=\"-1\">
        <tr>
            <td><label for='deviceType'>Тип</label></td>
            <td>
                <?php
                    include(dirname(__FILE__)."/selectTypeDevice.php");
                ?>
            </td>
        </tr>
        <tr>
            <td><label for='SensorTittle'>Наименование</label></td>
            <td><input id='SensorTittle' type=\"text\" name=\"Title\"></td>
        </tr>

    </table>

    <input class='btAdd' type='submit' value='OK' name='btAdd'>
    <input class='btCancel' type='button' onclick='history.back();' value='Cancel'>
</form>


