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


    if (isset($_REQUEST['btDelete'])) { //������� ������

        echo "<span style='color:blue;'>������ �� �� �������</span>";

    }
    else { // ��������� ��� ��������� ������

        if ( !isset($_REQUEST['Disabled']) ) {$_REQUEST['Disabled'] = "off";}

        if (!isset($_REQUEST['ID']) || !isset($_REQUEST['Disabled']) || !isset($_REQUEST['deviceType']) ||
            !isset($_REQUEST['deviceNet']) || !isset($_REQUEST['Adress']) || !isset($_REQUEST['setAlarm'])) {

        }

        //� ������ ������� ��� ��������� ��������� �� �����

        $arDevice = array(
            "DeviceID" =>  $_REQUEST['ID'],
            "Adress" =>  $_REQUEST['Adress'],
            "NetTypeID" =>  $_REQUEST['deviceNet'],
            "SensorTypeID" =>  $_REQUEST['deviceType'],
            "Disabled" =>  ($_REQUEST['Disabled'] == 'on')?1:0,
            "set_alarm" =>  $_REQUEST['setAlarm'],
        );

        $device = managerDevices::createDevice($arDevice);

        if (isset($_REQUEST['btAdd'])) { //��������� ������
            try {
                managerDevices::addDevice($device);
                unset($device);
                echo "<span style='color:blue;'>���������� ���������� � ���� ������</span>", "\n";
            }
            catch (Exception $e) { //���� ��������� ���������� ��� ������� ������ ����
                unset($device);
                echo "<span style='color:red;'>��������� ������ ��� ���������� ���������� � ��</span>";
                //echo $e->getErrorInfoHTML();
            }

        }
        elseif (isset($_REQUEST['btUpdate'])) { //�������� ������

        }

    }

?>

<div>
    <a href='index.php?pDevices=list#tabsDevices'><input class='btMainPage' type='button' value='�� �������'></a>
</div>

