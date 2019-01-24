<?php
/** Опрос всех температурных датчиков и запись показаний в базу данных
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:23
 */

require_once(dirname(__FILE__).'/class/lists.class.php');
require_once(dirname(__FILE__).'/class/managerUnits.class.php');
require_once(dirname(__FILE__).'/class/globalConst.interface.php');

$sel = new selectOption();
$sel->set('SensorTypeID', typeDevice::TEMPERATURE);
$sel->set('Disabled', 0);

$temperatureUnits = managerUnits::getListUnits($sel);

foreach ($temperatureUnits as $tekUnit) {
    $val = $tekUnit->getValue();
    if (!is_null($val)) {
        $tekUnit->writeValue($val);
    }
}

unset($temperatureUnits);

?>