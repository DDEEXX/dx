<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.01.19
 * Time: 12:11
 */

require_once(dirname(__FILE__)."/sqlDataBase.class.php");
require_once(dirname(__FILE__)."/unit.class.php");
require_once(dirname(__FILE__)."/lists.class.php");

class unitFactory {

    public static function build (array $value) {

        switch ($value['UniteTypeID']) {
            case typeUnit::TEMPERATURE :
                $className = 'temperatureUnit';
                break;
            default : $className = '';
        }

        if (class_exists($className)) {
            return new $className($value);
        } else {
            throw new \Exception("Неверный тип продукта");
        }

    }

}

class managerUnits
{

    public static function createDevice(array $value) {

        // Здесь создаём продукт с помощью Фабричного метода
        $unit = unitFactory::build($value);

        return $unit;

    }

    /**
     * Получить модули как объекты в виде массива
     * @param Iterator|null $sel
     * @return listUnits
     * @throws connectDBException
     * @throws querySelectDBException
     */
    public static function getListUnits(Iterator $sel = null){

        $arr = DB::getListUnits($sel);
        $list = new listUnits();
        foreach ($arr as $value) {
           $Unit = self::createDevice($value);
           $list->append($Unit);
        }
        return $list;

    }

}