<?php
/**
 *  Структура данных хранимых в разделяемой памяти
 * В сегменте с ключом, созданному по идентификатору проект PROJECT_LETTER_KEY = 'A' хранятся:
 * - ключ переменной KEY_1WARE_PATH = 3 : константа OWNETDir
 * - ключ переменной KEY_1WARE_ADDRESS = 4 : константа OWNetAddress
 *
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
        logger::writeLog('Ошибка при инициализации модулей в распределенную память. '.$mess,
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
     * sharedMemoryUnits constructor.
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
     * @return null|sharedMemoryUnits
     */
    public static function getInstance($projectID, $size = 12000)
    {
        if (!array_key_exists($projectID, self::$instance)) { //если еще не выделена память
            try {
                self::$instance[$projectID] = new sharedMemoryUnits($projectID, $size);
            } catch (shareMemoryInitUnitException $e) {
                self::$instance[$projectID] = null;
            }
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
     * Получить ID устройства модуля (из sm)
     * @param $label - метка модуля
     * @return int|null - id устройства
     */
    static public function getDeviceID($label) {
        $sm = self::getInstance(sharedMemory::PROJECT_LETTER_UNITS);
        if (is_null($sm)) {
            return null;
        }
        $listUnit = $sm->get(0);
        if (!is_array($listUnit)) {
            return null;
        }
        if (array_key_exists($label, $listUnit)) {
            $dataID = $listUnit[$label];
            if (is_array($dataID)) {
                if (array_key_exists('idDevice', $dataID)) {
                    return (int)$dataID['idDevice'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
        return null;
    }


    static public function getUnitID($id) {

        return null;

    }

    /**
     * Получить значение из разделяемой памяти по букве проекта и ключу
     * @param $projectID
     * @param $key
     * @return mixed|null
     */
    static public function getValue($projectID, $key) {
        $sm = self::getInstance($projectID);
        if (is_null($sm)) {
            return null;
        }
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
        //глобальные значения из БД
        try {
            $sm = sharedMemoryUnits::getInstance(sharedMemory::PROJECT_LETTER_KEY, sharedMemory::SIZE_MEMORY_KEY);
        } catch (shareMemoryInitUnitException $e) {
            return false;
        }
        if (!$sm->set(sharedMemory::KEY_1WARE_PATH, DB::getConst('OWNETDir'))) {return false;}
        if (!$sm->set(sharedMemory::KEY_1WARE_ADDRESS, DB::getConst('OWNetAddress'))) {return false;}

        //связь между наименованием модуля и ID модуля и устройства (для быстрого поиска без обращения к БД)
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

        //данные устройств
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