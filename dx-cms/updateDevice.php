<script>
    $(function() {
        $( ".btMainPage" ).button();
    });
</script>

<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 04.12.18
 * Time: 22:43
 */


    if (isset($_REQUEST['btDelete'])) { //Удаляем запись

        echo "<span style='color:blue;'>Данные из БД удалены</span>";

    }
    else { // обновляем или добавляем запись

        if ( !isset($_REQUEST['Disabled']) ) {$_REQUEST['Disabled'] = "off";}

        if (!isset($_REQUEST['ID']) || !isset($_REQUEST['Disabled']) || !isset($_REQUEST['deviceType']) ||
            !isset($_REQUEST['deviceNet']) || !isset($_REQUEST['Adress']) || !isset($_REQUEST['setAlarm'])) {

        }

        //В массив заносим все параметры пришедшие из форму

        $arDevice = array(
            "DeviceID" =>  $_REQUEST['ID'],
            "Adress" =>  $_REQUEST['Adress'],
            "NetTypeID" =>  $_REQUEST['deviceNet'],
            "SensorTypeID" =>  $_REQUEST['deviceType'],
            "Disabled" =>  ($_REQUEST['Disabled'] == 'on')?1:0,
            "set_alarm" =>  $_REQUEST['setAlarm'],
        );

        $device = managerDevices::createDevice($arDevice);

        if (isset($_REQUEST['btAdd'])) { //Добавляем запись
            try {
                managerDevices::addDevice($device);
                unset($device);
                echo "<span style='color:blue;'>Устройство добавленно в базу данных</span>", "\n";
            }
            catch (Exception $e) { //надо прописать исключение для каждоко своего типа
                unset($device);
                echo "<span style='color:red;'>Произошла ошибка при добавлении устройства в БД</span>";
                //echo $e->getErrorInfoHTML();
            }

        }
        elseif (isset($_REQUEST['btUpdate'])) { //Изменяем запись

        }

    }

?>

<div>
    <a href='index.php?pDevices=list#tabsDevices'><input class='btMainPage' type='button' value='На главную'></a>
</div>

