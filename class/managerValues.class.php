<?php

require_once dirname(__FILE__) . '/device.class.php';

class managerValues
{
    /*
     * Инициализировать связь между наименованием модуля и ID модуля и устройства
     * (для быстрого получения значения, без обращения к БД при создании объектов unit и device)
    */
    public static function initUnits()
    {
        //
        try {
            $sm = sharedMemoryUnits::getInstance(sharedMemory::PROJECT_LETTER_UNITS, sharedMemory::SIZE_MEMORY_UNITS);
        } catch (shareMemoryInitUnitException $e) {
            return false;
        }
        $listUnit = managerUnits::getListUnits();
        $smUnits = [];
        foreach ($listUnit as $tekUnit) {
            $device = $tekUnit->getDevice();
            $idDevice = null;
            if (!is_null($device)) {
                $idDevice = $device->getDeviceID();
            }
            $smUnits[$tekUnit->getLabel()] = ['idUnit'=>$tekUnit->getId(), 'idDevice'=>$idDevice];
        }
        if (!$sm->set(0, $smUnits)) {return false;}
        return true;
    }

}