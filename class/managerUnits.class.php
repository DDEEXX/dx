<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:11
 */

require_once(dirname(__FILE__) . "/sqlDataBase.class.php");
require_once(dirname(__FILE__) . '/unit.class.php');
require_once(dirname(__FILE__) . '/lists.class.php');
require_once(dirname(__FILE__) . '/globalConst.interface.php');
require_once(dirname(__FILE__) . '/logger.class.php');
require_once(dirname(__FILE__) . '/sharedMemory.class.php');

class unitFactory
{

    /**
     * @param array $value
     * @return mixed
     * @throws Exception
     */
    public static function build(array $value)
    {

        switch ($value['UniteTypeID']) {
            case typeUnit::TEMPERATURE :
                $className = 'temperatureUnit';
                break;
            case typeUnit::KEY_IN :
                $className = 'keyInUnit';
                break;
            case typeUnit::POWER_KEY :
                $className = 'powerKeyUnit';
                break;
            case typeUnit::PRESSURE :
                $className = 'pressureUnit';
                break;
            case typeUnit::HUMIDITY :
                $className = 'humidityUnit';
                break;
            default :
                $className = '';
        }

        if (class_exists($className)) {
            return new $className($value);
        }
        else {
            throw new Exception("Неверный тип продукта");
        }

    }

}

/**Класс для работы с устройствами
*/
class managerUnits
{
    /**
     * Иницилизация всех модулей.
     * Метод должет выполнятся самым первым, перед работой всех остальных модулей
     * Помещает все объекты модулей в распределяемую память
     * @return bool
     */
    public static function initUnits()
    {
        $smTypeUniteID = array(); //ключ - тип модуля, значение символ идентификатора проекта
        $smIdModule = array();    //ключ - id модуля, значение символ идентификатора проекта, в сегменте key=id
        $smLabelModule = array();
        $listProjectID = DB::getProjectIDUnits();
        foreach ($listProjectID as $projectID) {
            $projID = $projectID['ProjID'];
            if ($projID == sharedMemory::PROJECT_LETTER_KEY) { //зарезервированный служебный символ, нельзя использовать для модулей
                return false;
            }
            //Получает все модули в UniteType которых указан соответствующий projID (модули могут быть нескольких типов)
            $sel = new selectOption();
            $sel->set('ProjID', $projID);
            $listUnit = self::getListUnitsDB($sel);

            try {
                $sm = sharedMemoryUnits::getInstance($projID);
            } catch (shareMemoryInitUnitException $e) {
                return false;
            }

            //в одном сегменте разделяемой памяти могут быть модули с разными типами
            $typeUnit = array();
            //массив с id модулей
            $keys = array();
            foreach ($listUnit as $tekUnit) {
                $key = $tekUnit->initUnit($sm->getKey());
                if (is_null($key)) {
                    return false;
                }
                $keys[] = $key;
                //добавляем в массив (ключи), тип моделей UniteTypeID (1-temp? 2-label и т.д.)
                $typeUnit[$tekUnit->getType()] = true;
                //записываем в массив: ключ - id модуля, значение - буква проекта
                $smIdModule[$key] = $projID;
                //записываем в массив: ключ - label модуля, значение элемент массива: ключ - id модуля, значение - буква проекта
                $smLabelModule[$tekUnit->getLabel()] = array('id_module'=>$key, 'project_id'=>$projID);
            }
            //Записываем с ключем 0 массив содержащий ID модулей, понадобится для дальнейшего доступа
            $sm->set(sharedMemory::KEY_UNIT_ID, $keys);
            //записываем в массив: ключ - тип модуля, значение - буква проекта
            foreach ($typeUnit as $key=>$value) {
                $smTypeUniteID[$key] = $projID;
            }
        }
        unset($listProjectID);

        try {
            $sm = sharedMemoryUnits::getInstance(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::MEMORY_SIZE_KEY);
        } catch (shareMemoryInitUnitException $e) {
            return false;
        }
        //Записываем с ключем 0 массив содержащий в ключах тип модуля, в значениях букву projectID
        if (!$sm->set(sharedMemory::KEY_UNIT_TYPE, $smTypeUniteID)) {return false;}
        if (!$sm->set(sharedMemory::KEY_ID_MODULE, $smIdModule)) {return false;}
        if (!$sm->set(sharedMemory::KEY_LABEL_MODULE, $smLabelModule)) {return false;}
        if (!$sm->set(sharedMemory::KEY_1WARE_PATH, DB::getConst('OWNETDir'))) {return false;}
        if (!$sm->set(sharedMemory::KEY_1WARE_ADDRESS, DB::getConst('OWNetAddress'))) {return false;}

        //Для поиска модулей в sm по подписке MQQT, создадим массив
        $listUnitsMQQTTopicStatus = self::getListUnitsMQQTTopicStatus(0);
        if (!$sm->set(sharedMemory::KEY_MQQT_STATUS_TOPIC, $listUnitsMQQTTopicStatus)) {return false;}
        return true;
    }

    /**
     * Получить модули как объекты в виде массива из распределяемой памяти
     * @param null $unitType
     * @param null $deviceDisables
     * @return listUnits
     */
    public static function getListUnits($unitType = null, $deviceDisables = null)
    {
        return sharedMemoryUnits::getListUnits($unitType, $deviceDisables);
    }

    /**
     * Получить список модулей на шине 1-Wire опрашиваемые по команде Read Conditional Search ROM (условный поиск)
     * @param null $deviceDisables
     * @return array одномерный массив ключ id модуля, значение адрес в 1-wire сети
     */
    public static function getListUnits1WireLoop($deviceDisables = null) {
        //Получаем все устройства
        $listUnits1WireLoop = array();
        $listAllUnits = self::getListUnits(null , $deviceDisables);
        foreach ($listAllUnits as $unit) {
            if (is_null($unit)) {
                continue;
            }
            if ($unit->check1WireLoop()) {
                $disabled = $unit->checkDeviceDisabled();
                if (is_null($disabled)) {
                    continue;
                }
                elseif (!is_null($deviceDisables)){
                    if ($disabled!=$deviceDisables) {
                        continue;
                    }
                }
                $listUnits1WireLoop[$unit->getId()] = $unit->getDeviceAdress();
            }
        }
        return $listUnits1WireLoop;
    }

    /**
     *  Обновляет set_alarm у 1wire устройств
     */
    public static function setAlias1WireUnit() {

        $MAX_CHECK_1WIRE_DIR = 10;  //количество попыток
        $PAUSE_CHECK_1WIRE_DIR = 5; //пауза между попытками

        //т.к. взаимодействие с 1wire идет через файловую систему сначала ждем пока будут доступен
        //соответствующий каталог

        $n = 1; //номер попытки обращения к каталогу
        $is1wire = self::check1WireDir();
        while ((!$is1wire) && $n<=$MAX_CHECK_1WIRE_DIR) {
            sleep($PAUSE_CHECK_1WIRE_DIR);
            $is1wire = self::check1WireDir();
        }

        if (!$is1wire) {
            logger::writeLog("При попытки обновить set_alarm не найден путь до каталога 1wire", loggerTypeMessage::WARNING ,loggerName::ACCESS);
            return;
        }

        $listUnit1WireLoop = managerUnits::getListUnits1WireLoop(0);
        foreach ($listUnit1WireLoop as $uniteID => $address) {
            $unite = self::getUnitID($uniteID);
            $unite->updateDeviceAlarm();
        }
    }

    public static function check1WireDir() {
        $OWNetDir = sharedMemoryUnits::getValue(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::KEY_1WARE_PATH);
        if (is_null($OWNetDir)) {
            return false;
        }
        $dir1WireAlarm = $OWNetDir.'/alarm';
        return is_dir($dir1WireAlarm);
    }

    /**
     * Получить список модулей устройства которых это MQQT клиенты, и у них есть подписки статуса
     * @param int $deviceDisables
     * @return array Одномерный массив ключ id модуля, значение тема сообщения
     */
    public static function getListUnitsMQQTTopicStatus($deviceDisables = 0)
    {
        $listUnitsMQQTLoop = array();
        //Получаем все устройства
        $listAllUnits = self::getListUnits(null , $deviceDisables);
        foreach ($listAllUnits as $unit) {
            $disabled = $unit->checkDeviceDisabled();
            if (is_null($disabled)) {
                continue;
            }
            if ($disabled!=$deviceDisables) {
                continue;
            }
            $topicStatus = $unit->checkMQQTTopicStatus();
            if (isset($topicStatus)) {
                $listUnitsMQQTLoop[$unit->getId()] = $topicStatus;
            }
        }
        return $listUnitsMQQTLoop;
    }

    /**
     * Ищет модуль по имени в распределенной памяти. Если модуля с таким именем нет, то возвращает null
     * @param $label
     * @return mixed|null
     */
    public static function getUnitLabel($label)
    {
        return sharedMemoryUnits::getUnitLabel($label);
    }

    /**
     * Ищет модуль по ID в распределенной памяти. Если модуля с таким именем нет, то возвращает null
     * @param $id
     * @return mixed|null
     */
    public static function getUnitID($id)
    {
        return sharedMemoryUnits::getUnitID($id);
    }

    /**
     * Ищет модули по подписке в распределенной памяти.
     * @param $topic
     * @return array - массив с id модулей
     */
    public static function getUnitStatusTopic($topic)
    {
        return sharedMemoryUnits::getUnitStatusTopic($topic);
    }

    /**
     * Считывает данные с датчика, обновляет значение и дату считывания, помещает объект модуля в
     *распределяемую память
     * @param unit $unit
     * @return null int
     */
    public static function updateValueUnit(unit $unit) {
        $unit->updateValue();
        return sharedMemoryUnit::set($unit);
    }

    /** Создает получает объект модуля по параметрам в $value
     * @param array $value
     * @return |humidityUnit|keyInUnit|mixed|powerKeyUnit|pressureUnit|temperatureUnit|null
     */
    public static function createUniteDB(array $value)
    {
        // Здесь создаём продукт с помощью Фабричного метода
        try {
            return unitFactory::build($value);
        } catch (Exception $e) {
            logger::writeLog('Ошибка при создании объекта модуля (managerUnits.class.php)'.$e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }
        return null;
    }

    /**
     * Получить модули как объекты в виде массива из базы данных
     * @param Iterator|null $sel
     * @return listUnits
     */
    public static function getListUnitsDB(Iterator $sel = null)
    {
        $list = new listUnits();
        $arr = DB::getListUnits($sel);
        foreach ($arr as $value) {
            $Unit = self::createUniteDB($value);
            $list->append($Unit);
        }
        unset($arr);
        return $list;
    }

    /**
     * Ищет модуль по имени в базе данных. Если модуля с таким именем нет, то возвращает null
     * @param $label
     * @return mixed|null
     */
    public static function getUnitLabelDB($label)
    {

        $sel = new selectOption();
        $sel->set('UnitLabel', $label);

        $listUnits = self::getListUnitsDB($sel);

        $resUnit = null;

        foreach ($listUnits as $tekUnit) {
            $resUnit = $tekUnit;
        }

        unset($listUnits);

        return $resUnit;

    }
}