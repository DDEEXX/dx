<?php
/* Структура данных хранимых в разделяемой памяти
 * В сегменте с ключом, созданному по идентификатору проект PROJECT_LETTER_KEY = 'A' хранятся:
 * - ключ переменной KEY_UNIT_TYPE = 0 : массив [uniteType=>projID]
 * - ключ переменной KEY_ID_MODULE = 1 : массив [uniteID=>projID]
 * - ключ переменной KEY_LABEL_MODULE = 2 : массив [uniteLabel=>['id_module'=>UniteID, 'project_id'=>projID]]
 * - ключ переменной KEY_1WARE_PATH = 3 : константа OWNETDir
 * - ключ переменной KEY_1WARE_ADDRESS = 4 : константа OWNetAddress
 *
 * В сегменте с ключом, созданному по идентификатору проект projID [B..Z] хранятся:
 * - ключ переменной 0 : массив [uniteID1, uniteID2, ...]
 * - ключ переменной uniteID1 - объект модуля
 * - ключ переменной uniteID2 - объект модуля
 *
 */

require_once(dirname(__FILE__) . '/globalConst.interface.php');
require_once(dirname(__FILE__) . '/managerUnits.class.php');
require_once(dirname(__FILE__) . '/managerDevices.class.php');
require_once(dirname(__FILE__) . '/logger.class.php');

class shareMemoryInitUnitException extends Exception
{
    public function __construct($mess)
    {
        parent::__construct($mess);
        logger::writeLog('Ошибка при инициализации модулей в распределенную память.'.$mess,
            loggerTypeMessage::ERROR,
            loggerName::ERROR);
    }
}

class sharedMemoryDeviceData {

    static public function set($idDevice, $data) {
        $sm = sharedMemoryUnits::getInstance(sharedMemory::PROJECT_LETTER_DATA_DEVICE, sharedMemory::SIZE_MEMORY_DATA_DEVICE);
        return $sm->set($idDevice, $data);
    }

}

class sharedMemoryUnit
{
    /** Запись в разделяемую память модуля
     * @param unit $unit
     * @return null
     */
    static public function set(unit $unit)
    {
        $smKey = $unit->getSmKey();
        $shmID = shm_attach($smKey);
        $semID = sem_get($smKey);
        $idUnit = $unit->getId();
        if ($shmID === false) {
            logger::writeLog("При инициализации модуля с id ".$idUnit." ошибка в shm_attach()", loggerTypeMessage::ERROR, loggerName::ERROR);
            return null;
        }
        if ($semID === false) {
            logger::writeLog("При инициализации модуля с id ".$idUnit." ошибка в sem_get()", loggerTypeMessage::ERROR, loggerName::ERROR);
            return null;
        }
        $error = false;
        if (!sem_acquire($semID)) $error = true;
        if (!shm_put_var($shmID, $idUnit, $unit)) $error = true;
        if (!sem_release($semID)) $error = true;
        if ($error) {
            logger::writeLog("ошибка записи модуля с id ".$idUnit." в распределяемую память", loggerTypeMessage::ERROR, loggerName::ERROR);
            return null;
        }
        return $idUnit;
    }
}

class sharedMemoryUnits
{
    const FILE_PATH = __FILE__;
    protected $key;         //числовой идентификатор сегмента разделяемой памяти
    protected $shmID;       //идентификатор, для доступа к разделяемой памяти
    protected $semID;       //идентификатор, для доступа к семафору

    private static $instance = []; //массив объектов sharedMemoryUnits

    /** Получить числовой идентификатор сегмента разделяемой памяти
     * @return int
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * sharedMemoryUnit constructor.
     * @param string $projectID
     * @param int $size
     * @throws shareMemoryInitUnitException
     */
    protected function __construct($projectID, $size = 10000)
    {
        $this->key = ftok(self::FILE_PATH, $projectID); //получаем ключ по пути и идентификатору
        $this->shmID = shm_attach($this->key, $size); //первый вызов создает сегмент выделенной памяти, последующий возвращает только указатель
        $this->semID = sem_get($this->key);
        if (!$this->shmID) {
            $mess = 'Не определен идентификатор для доступа к разделяемой памяти';
            logger::writeLog($mess);
            throw new shareMemoryInitUnitException($mess);
        }
        if (!$this->semID) {
            $mess = 'Не определен идентификатор для доступа к семафору';
            logger::writeLog($mess);
            throw new shareMemoryInitUnitException($mess);
        }
    }

    /**
     * Создание или получает объект по идентификатору для работы с разделяемой памятью
     * @param string $projectID
     * @param int $size
     * @return sharedMemoryUnits
     * @throws shareMemoryInitUnitException
     */
    public static function getInstance($projectID, $size = 12000)
    {
        if (!array_key_exists($projectID, self::$instance)) { //если еще не выделена память
            self::$instance[$projectID] = new sharedMemoryUnits($projectID, $size);
        }
        return self::$instance[$projectID];
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function set($key, $value)
    {
        $error = false;
        if (!sem_acquire($this->semID)) $error = true;
        if (!shm_put_var($this->shmID, $key, $value)) $error = true;
        if (!sem_release($this->semID)) $error = true;
        if ($error) return false;
        return true;
    }

    public function get($key)
    {
        sem_acquire($this->semID);
        $value = shm_has_var($this->shmID, $key) ? @shm_get_var($this->shmID, $key) : null;
        sem_release($this->semID);
        return $value;
    }

    /**
     * Возвращает из распределяемой памяти модуль по имени
     * @param $label - имя модуля
     * @return mixed|null
     */
    static public function getUnitLabel($label) {

//        try {
//            $sm = self::getInstance(sharedMemory::PROJECT_LETTER_KEY);
//        } catch (shareMemoryInitUnitException $e) {
//            return null;
//        }
//
//        $unitsLabels = $sm->get(sharedMemory::KEY_LABEL_MODULE);
//        if (array_key_exists($label, $unitsLabels)) {
//            $idModule = $unitsLabels[$label]['id_module'];
//            $projectID = $unitsLabels[$label]['project_id'];
//        }
//        else {
//            return null;
//        }
//
//        if (is_null($idModule) || is_null($projectID)) {
//            return null;
//        }
//
//        try {
//            $sm = self::getInstance($projectID);
//        } catch (shareMemoryInitUnitException $e) {
//            return null;
//        }
//
//        return $sm->get($idModule);

            return null;

    }

    static public function getUnitID($id) {

//        try {
//            $sm = self::getInstance(sharedMemory::PROJECT_LETTER_KEY);
//        } catch (shareMemoryInitUnitException $e) {
//            return null;
//        }
//
//        $unitsID = $sm->get(sharedMemory::KEY_ID_MODULE);
//        if (array_key_exists($id, $unitsID)) {
//            $idModule = $id;
//            $projectID = $unitsID[$id];
//        }
//        else {
//            return null;
//        }
//
//        if (is_null($idModule) || is_null($projectID)) {
//            return null;
//        }
//
//        try {
//            $sm = self::getInstance($projectID);
//        } catch (shareMemoryInitUnitException $e) {
//            return null;
//        }
//
//        return $sm->get($idModule);

            return null;

    }

    /**
     * Получить значение из разделяемой памяти по букве проекта и ключу
     * @param $projectID
     * @param $key
     * @return mixed|null
     */
    static public function getValue($projectID, $key) {

        try {
            $sm = self::getInstance($projectID);
        } catch (shareMemoryInitUnitException $e) {
            return null;
        }

        //Получаем массив с указателями на сегменты распределяемой памяти, ключ - тип модуля
        return $sm->get($key);

    }

}

class managerSharedMemory {

    /**
     * Инициализация всех модулей.
     * Метод должен выполнятся самым первым, перед работой всех остальных модулей.
     * Помещает данные модулей в распределяемую память.
     * @return bool
     */
    public static function init() {
        try {
            $sm = sharedMemoryUnits::getInstance(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::SIZE_MEMORY_KEY);
        } catch (shareMemoryInitUnitException $e) {
            return false;
        }
        if (!$sm->set(sharedMemory::KEY_1WARE_PATH, DB::getConst('OWNETDir'))) {return false;}
        if (!$sm->set(sharedMemory::KEY_1WARE_ADDRESS, DB::getConst('OWNetAddress'))) {return false;}

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

        $listDevice = managerDevices::getListDevices();
        foreach ($listDevice as $device) {
            $idDevice = $device->getDeviceID();
            $dataDevice = new deviceData($idDevice);
            if (!$dataDevice->setData()) {
                return false;
            }
        }
        return true;
    }

}