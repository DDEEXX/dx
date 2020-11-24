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

class managerUnits
{

    public static function createDevice(array $value)
    {
        // Здесь создаём продукт с помощью Фабричного метода
        try {
            $unit = unitFactory::build($value);
            return $unit;
        } catch (Exception $e) {
            logger::writeLog('Ошибка при создании объекта модуля (managerUnits.class.php)'.$e->getMessage(),
                loggerTypeMessage::ERROR, loggerName::ERROR);
        }

        return null;
    }

    /**
     * Получить модули как объекты в виде массива
     * @param Iterator|null $sel
     * @return listUnits
     */
    public static function getListUnits(Iterator $sel = null)
    {
        $list = new listUnits();

        $arr = DB::getListUnits($sel);

        foreach ($arr as $value) {
            $Unit = self::createDevice($value);
            $list->append($Unit);
        }
        return $list;
    }

    /**
     * Ищет модуль по имени. Если модуля с таким именем нет, то возвращает null
     * @param $label
     * @return mixed|null
     */
    public static function getUnitLabel($label)
    {

        $sel = new selectOption();
        $sel->set('UnitLabel', $label);

        $listUnits = self::getListUnits($sel);

        $resUnit = null;

        foreach ($listUnits as $tekUnit) {
            $resUnit = $tekUnit;
        }

        unset($listUnits);

        return $resUnit;

    }

}