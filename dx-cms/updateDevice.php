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

        if (!isset($_REQUEST['ID']) || !isset($_REQUEST['Disabled']) || !isset($_REQUEST['deviceType']) ||
            !isset($_REQUEST['deviceNet']) || !isset($_REQUEST['Adress']) || !isset($_REQUEST['setAlarm'])) {

        }

        //� ������ ������� ��� ��������� ��������� � �����
        $arDevice = array(
            "DeviceID" =>  $_REQUEST['ID'],
            "Adress" =>  $_REQUEST['Adress'],
            "NetTypeID" =>  $_REQUEST['deviceNet'],
            "SensorTypeID" =>  $_REQUEST['deviceType'],
            "Disabled" =>  $_REQUEST['Disabled'],
            "set_alarm" =>  $_REQUEST['setAlarm'],
        );

        if (isset($_REQUEST['btAdd'])) { //��������� ������
            try {



                echo "<span style='color:blue;'>���������� ���������� � ���� ������</span>", "\n";

                throw new Exception();

            }
            catch (Exception $e) { //���� ��������� ���������� ��� ������� ������ ����
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

