<div class="grid_12 alpha omega">
    <div class="ui-corner-all ui-state-default ui-widget-content" style="height: 735px">
        <h2 style="margin-left:5px; margin-bottom: 5px">Состояние устройств</h2>

        <table style="margin-left: 5px">
            <thead class="ui-widget-header">
                <tr'>
                    <th style="width: 20px"></th>
                    <th>ID</th>
                    <th>Наименование</th>
                    <th>Сеть</th>
                    <th>Тип</th>
                    <th>Данные</th>
                </tr>
            </thead>
            <tbody>

            <?php

            include_once 'class/managerDevices.class.php';

            function getTitleTestStatus($status, $date) {
                switch ($status) {
                    case testDeviceCode::WORKING : $result = 'WORKING'; break;
                    case testDeviceCode::NO_CONNECTION : $result = 'NO CONNECTION'; break;
                    case testDeviceCode::NO_DEVICE : $result = 'NO DEVICE'; break;
                    case testDeviceCode::DISABLED : $result = 'DISABLED'; break;
                    case testDeviceCode::ONE_WIRE_ADDRESS : $result = 'ERROR 1WIRE ADDRESS'; break;
                    case testDeviceCode::ONE_WIRE_ALARM : $result = 'ERROR 1WIRE ALARM'; break;
                    case testDeviceCode::NO_VALUE : $result = 'NO VALUE'; break;
                    case testDeviceCode::IS_MQTT_DEVICE : $result = 'IS MQTT DEVICE'; break;
                    case testDeviceCode::NO_TEST : $result = 'NO TEST'; break;
                    default  : $result = '';
                }
                if (!is_null($date)) {
                    $result = $result. ' (' .$date.')';
                }
                return $result;
            }

            /**Получить список всех физ. устройств*/
            $listDevices = managerDevices::getListDevices();
            $devicesTestCode = managerDevices::getLastTestCode();

            foreach ($listDevices as $key => $device) {

                $deviceID = $device->getDeviceID();
                $netTitle = $device->getNet();
                $deviceType = $device->getType();
                $deviceData = $device->getData();
                $deviceNote = $device->getNote();
                $deviceStatus = testDeviceCode::NO_TEST;
                $deviceStatusDate = null;
                if (array_key_exists($deviceID, $devicesTestCode)) {
                    $deviceStatus = $devicesTestCode[$deviceID]['Code'];
                    $deviceStatusDate = $devicesTestCode[$deviceID]['Date'];
                }
                if (is_null($deviceStatus)) {$deviceStatus = testDeviceCode::NO_TEST;}

                $title = getTitleTestStatus($deviceStatus, $deviceStatusDate);

                echo '<tr>';
                echo '<td style="padding-left: 5px; padding-top: 5px">
                        <div class="test_status test_status_code_'.$deviceStatus.'" title="'.$title.'" style="width: 10px; height: 10px; border-radius: 5px"></div>
                        </td>';
                echo '<td style="padding-left: 5px; padding-right: 10px">',$deviceID,'</td>';
                echo '<td>',$deviceNote,'</td>';
                echo '<td>',$netTitle,'</td>';
                echo '<td>',$deviceType,'</td>';
                echo '<td>',$deviceData,'</td>';
                echo '</tr>';

            }

            unset($listDevices); //!!! наверное надо освобождать каждый объект в массиве, а не массив целиком

            ?>

            </tbody>
        </table>

    </div>
</div>
