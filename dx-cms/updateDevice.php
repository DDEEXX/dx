<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 04.12.18
 * Time: 22:43
 */


    if (isset($_REQUEST['btDelete'])) { //������� ������


    }
    else { // ��������� ��� ��������� ������

        if (!isset($_REQUEST['ID']) || !isset($_REQUEST['Disabled']) || !isset($_REQUEST['deviceType']) ||
            !isset($_REQUEST['deviceNet']) || !isset($_REQUEST['Adress']) || !isset($_REQUEST['setAlarm'])) {

        }

        if (isset($_REQUEST['btAdd'])) { //��������� ������

        }
        elseif (isset($_REQUEST['btUpdate'])) { //�������� ������

        }

    }
?>