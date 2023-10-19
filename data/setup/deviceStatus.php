<div class="grid_12 alpha omega"
     style="display: flex; flex-direction: column; align-content: flex-start; align-self: stretch; flex-grow: 1">
    <div class="ui-corner-all ui-state-default ui-widget-content"
         style="align-self: stretch; flex-grow: 1">
        <h2 style="margin-left:5px; margin-bottom: 5px">Состояние устройств</h2>
        <div style="display: flex; flex-direction: column; align-content: flex-start; max-height: 715px; overflow: auto; margin-right: 5px">
        <table style="margin-left: 5px">
            <thead class="ui-widget-header">
            <tr>
                <th style="width: 20px"></th>
                <th>ID</th>
                <th>Наименование</th>
                <th>Сеть</th>
                <th>Тип</th>
                <th style="padding-left: 5px">Данные</th>
            </tr>
            </thead>
            <tbody>

            <?php

            include_once 'class/managerDevices.class.php';

            function getTitleTestStatus($status, $date)
            {
                switch ($status) {
                    case testDeviceCode::WORKING :
                        $result = 'WORKING';
                        break;
                    case testDeviceCode::NO_CONNECTION :
                        $result = 'NO CONNECTION';
                        break;
                    case testDeviceCode::NO_DEVICE :
                        $result = 'NO DEVICE';
                        break;
                    case testDeviceCode::DISABLED :
                        $result = 'DISABLED';
                        break;
                    case testDeviceCode::ONE_WIRE_ADDRESS :
                        $result = 'ERROR 1WIRE ADDRESS';
                        break;
                    case testDeviceCode::ONE_WIRE_ALARM :
                        $result = 'ERROR 1WIRE ALARM';
                        break;
                    case testDeviceCode::NO_VALUE :
                        $result = 'NO VALUE';
                        break;
                    case testDeviceCode::IS_MQTT_DEVICE :
                        $result = 'IS MQTT DEVICE';
                        break;
                    case testDeviceCode::NO_TEST :
                        $result = 'NO TEST';
                        break;
                    default  :
                        $result = '';
                }
                if (!is_null($date)) {
                    $result = $result . ' (' . $date . ')';
                }
                return $result;
            }

            function getNetTitle($netCode)
            {
                switch ($netCode) {
                    case netDevice::NONE :
                        $result = '--';
                        break;
                    case netDevice::ONE_WIRE :
                        $result = '1 wire';
                        break;
                    case netDevice::ETHERNET_JSON :
                        $result = 'ethernet';
                        break;
                    case netDevice::PIO :
                        $result = 'pio';
                        break;
                    case netDevice::I2C :
                        $result = 'i2c';
                        break;
                    case netDevice::ETHERNET_MQTT :
                        $result = 'mqtt';
                        break;
                    default  :
                        $result = '';
                }
                return $result;
            }

            function getImageDevice($deviceType)
            {
                switch ($deviceType) {
                    case typeDevice::TEMPERATURE :
                        $result = 'img2/icon_small/thermometer.png';
                        break;
                    case typeDevice::LABEL :
                        $result = '';
                        break;
                    case typeDevice::POWER_KEY :
                    case typeDevice::SWITCH_WHD02 :
                    case typeDevice::KEY_OUT :
                        $result = 'img2/icon_small/power.png';
                        break;
                    case typeDevice::KEY_IN :
                        $result = 'img2/icon_small/keyin.png';
                        break;
                    case typeDevice::PRESSURE :
                        $result = 'img2/icon_small/barometer.png';
                        break;
                    case typeDevice::HUMIDITY :
                        $result = 'img2/icon_small/humidity.png';
                        break;
                    case typeDevice::KITCHEN_HOOD :
                        $result = 'img2/icon_small/fan.png';
                        break;
                    case typeDevice::GAS_SENSOR :
                        $result = 'img2/icon_small/sirens.png';
                        break;
                    default  :
                        $result = '';
                }
                return $result;
            }

            function getStatus($status) {
                $result = array_search($status, statusKeyData::status);
                if ($result === false) {
                    $result = '';
                }
                return $result;
            }

            /**Получить список всех физ. устройств*/
            $listDevices = managerDevices::getListDevices();
            $devicesTestCode = managerDevices::getLastTestCode();

            foreach ($listDevices as $key => $device) {

                $deviceID = $device->getDeviceID();
                $netTitle = getNetTitle($device->getNet());
                $deviceImage = getImageDevice($device->getType());
                $value = '';
                switch ($device->getDevicePhysic()->getFormatValue()) {
                    case formatValueDevice::MQTT_KITCHEN_HOOD:
                    case formatValueDevice::MQTT_GAS_SENSOR:
                        //TODO - придумать с value
                        break;
                    default :
                        $deviceData = $device->getData()->getDataArray();
                        $value = 'v: '.($deviceData['valueNull']?'null': $deviceData['value']).
                            ', d: '.date('H:i:s d-m-Y', $deviceData['date']).
                            ', s: '.getStatus($deviceData['status']);
                }
                $deviceNote = $device->getNote();
                $deviceStatus = testDeviceCode::NO_TEST;
                $deviceStatusDate = null;
                if (array_key_exists($deviceID, $devicesTestCode)) {
                    $deviceStatus = $devicesTestCode[$deviceID]['Code'];
                    $deviceStatusDate = $devicesTestCode[$deviceID]['Date'];
                }
                if (is_null($deviceStatus)) {
                    $deviceStatus = testDeviceCode::NO_TEST;
                }
                $title = getTitleTestStatus($deviceStatus, $deviceStatusDate);

                echo '<tr>';
                echo '<td style="padding-left: 5px; padding-top: 5px">
                        <div class="test_status test_status_code_' . $deviceStatus . '" title="' . $title . '" style="width: 10px; height: 10px; border-radius: 5px"></div>
                        </td>';
                echo '<td style="padding-left: 5px; padding-right: 5px">', $deviceID, '</td>';
                echo '<td style="padding-left: 5px; padding-right: 5px">', $deviceNote, '</td>';
                echo '<td style="padding-left: 5px; padding-right: 5px">', $netTitle, '</td>';
                echo '<td><img src="' . $deviceImage . '" alt=""></td>';
                echo '<td style="padding-left: 5px">', $value, '</td>';
                echo '</tr>';
//break;
            }

            unset($listDevices); //!!! наверное надо освобождать каждый объект в массиве, а не массив целиком

            ?>

            </tbody>
        </table>
        </div>
    </div>
</div>
