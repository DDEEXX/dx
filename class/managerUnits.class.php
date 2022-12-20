<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:11
 */

require_once(dirname(__FILE__) . '/sqlDataBase.class.php');
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
            case typeUnit::KEY_OUT :
                $className = 'KeyOutUnit';
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
            throw new Exception('Неверный тип продукта');
        }

    }

}

/**Класс для работы с устройствами
*/
class managerUnits
{

    /**
     * Получить ID устройства по метке модуля
     * @param $label - метка модуля
     * @return int|null - ID устройства
     */
    public static function getIdDevice($label) {
        return sharedMemoryUnits::getDeviceID($label);
    }

    /**
     * Ищет модуль по имени в базе данных. Если модуля с таким именем нет, то возвращает null
     * @param $label
     * @return iUnit|null
     */
    public static function getUnitLabel($label)
    {
        $result = null;
        $sel = new selectOption();
        $sel->set('UnitLabel', $label);
        $listUnits = self::getListUnits($sel);
        foreach ($listUnits as $tekUnit) {
            $result = $tekUnit;
        }
        unset($listUnits);
        return $result;
    }

    /**
     * Ищет модуль по ID в распределенной памяти. Если модуля с таким именем нет, то возвращает null
     * @param $id
     * @return iUnit|null
     */
    public static function getUnitID($id)
    {
        return sharedMemoryUnits::getUnitID($id);
    }

    /** Создает получает объект модуля по параметрам в $value
     * @param array $value
     * @return |humidityUnit|keyInUnit|mixed|powerKeyUnit|pressureUnit|temperatureUnit|null
     */
    private static function createUnite(array $value)
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
    public static function getListUnits(Iterator $sel = null)
    {
        $list = new listUnits();
        $arr = DB::getListUnits($sel);
        foreach ($arr as $value) {
            $unit = self::createUnite($value);
            if (!is_null($unit)) {
                $list->append($unit);
            }
        }
        unset($arr);
        return $list;
    }

    public static function testUnits() {

        //Получаем список всех включенных модулей
        $Units = managerUnits::getListUnits(null, 0);

        $result = array();

        foreach ($Units as $unit) {

            if (is_null($unit)) continue;

            if (!is_null($unit->getDevice())) {
                $testCode = $unit->test();
            }
            else {
                $testCode = testUnitCode::NO_DEVICE;
            }

            $result[$unit->getId()] = $testCode;

        }

        unset($Units);

        return $result;

    }

}

class unitsValuesHistory {

    static public function saveDataToDB(iSensorUnite $unit, iDeviceDataValue $data) {

        $uniteID = $unit->getId();
        if ($data->valueNull === true) {
            logger::writeLog('Попытка записать в базу данных NULL значение модуля id='.$uniteID,
                loggerTypeMessage::WARNING, loggerName::ACCESS);
            return;
        }
        if (!is_a($unit, 'sensorUnit')) {
            logger::writeLog('Попытка записать в базу данных значение модуля не являющемся сенсором id='.$uniteID,
                loggerTypeMessage::WARNING, loggerName::ACCESS);
            return;
        }

        $delta = $unit->getDelta();
        $value = (float)$data->value + (float)$delta;
        $nameTabValue = 'tvalue_' . $unit->getValueTable();
        $dateValue = date('Y-m-d H:i:s',$data->date);

        $query = 'INSERT INTO ' . $nameTabValue . ' VALUES (NULL, ' . "$uniteID,"." '$dateValue',"  . $value . ')';

        try {
            $con = sqlDataBase::Connect();
            $result = queryDataBase::execute($con, $query);
            if (!$result) {
                logger::writeLog('Ошибка при записи в базу данных (writeValue)',
                    loggerTypeMessage::ERROR, loggerName::ERROR);
            }
        } catch (connectDBException $e) {
            logger::writeLog('Ошибка при подключении к базе данных',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        } catch (querySelectDBException $e) {
            logger::writeLog('Ошибка при добавлении данных в базу данных',
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

        unset($con);

    }

}