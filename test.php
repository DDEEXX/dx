<?php
/** Опрос всех температурных датчиков и запись показаний в базу данных
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:23
 */

require_once(dirname(__FILE__) . '/class/globalConst.interface.php');
require_once(dirname(__FILE__) . '/class/lists.class.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

$sel = new selectOption();
$sel->set('SensorTypeID', typeDevice::PRESSURE);
$sel->set('Disabled', 0);

$pressureUnits = managerUnits::getListUnits($sel);

foreach ($pressureUnits as $tekUnit) {
    $tekUnit->getAverageForInterval(10);
}

unset($pressureUnits);
