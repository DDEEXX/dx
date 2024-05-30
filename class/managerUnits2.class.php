<?php

namespace units2;

require_once(dirname(__FILE__) . '/list2.class.php');
require_once(dirname(__FILE__) . '/units2.class.php');
require_once(dirname(__FILE__) . '/sqlDB.class.php');

class managerUnits
{
    static public function getUnitLabel($label)
    {
        $result = null;
        $filter = new unitsFilter();
        $filter->append('Label', $label);
        $units = self::getListUnits($filter);
        if (is_array($units) && count($units) > 0)
            $result = $units[0];
        return $result;
    }

    static public function getListUnits(iListFilter $filter)
    {
        $result = [];
        $dataUnits = DB::getUnits($filter);
        return $result;
    }

}

class unitFactory
{
    public function createUnite()
    {

    }
}