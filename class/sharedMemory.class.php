<?php
/* Структура данных хранимых в разделяемой памяти
 * В сегменте с ключом, созданному по идентификатору проект ROJECT_LETTER_KEY = 'A' хранятся:
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
require_once(dirname(__FILE__) . '/logger.class.php');

class shareMemoryInitUnitException extends Exception
{
    public function __construct($mess)
    {
        parent::__construct($mess);
        logger::writeLog('Ошибка при иницилизации модулей в распределенную память.'.$mess,
            loggerTypeMessage::ERROR,
            loggerName::ERROR);
    }
}

class sharedMemoryUnit
{
    /** запись в разделяемую память модуля
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
            logger::writeLog("При иницилизации модуля с id ".$idUnit." ошибка в shm_attach()", loggerTypeMessage::ERROR, loggerName::ERROR);
            return null;
        }
        if ($semID === false) {
            logger::writeLog("При иницилизации модуля с id ".$idUnit." ошибка в sem_get()", loggerTypeMessage::ERROR, loggerName::ERROR);
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

    private static $instance = array(); //массив объектов sharedMemoryUnits

    /** получить числовой идентификатор сегмента разделяемой памяти
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

    /** Создание или получает объект по идентификатору для работы с разделяемой памятью
     * @param string $projectID
     * @param int $size
     * @return sharedMemoryUnits
     * @throws shareMemoryInitUnitException
     */
    public static function getInstance($projectID, $size = 10000)
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
        $value = @shm_get_var($this->shmID, $key);
        sem_release($this->semID);

        return $value;
    }

    static public function getListUnits($unitType, $deviceDisables)
    {

        //Определяем указатель на сегмент распределяемой памяти (точнее символ проекта) в котором хранятся
        //модуля с типом $sensorTypeID
        $arrProjectID = array();
        try {
            $sm = self::getInstance(sharedMemory::PROJECT_LETTER_KEY);
        } catch (shareMemoryInitUnitException $e) {
            return new listUnits();
        }

        //Получаем массив с указателями на сегменты распределяемой памяти, ключ - тип модуля
        $arrTypeUniteID = $sm->get(sharedMemory::KEY_UNIT_TYPE);
        foreach ($arrTypeUniteID as $key => $value) {
            if (is_null($unitType)) {   //Если нет отбора по типу модуля, то берем все сегменты
                $arrProjectID[] = $value;
            }
            else {
                if ($key == $unitType) {
                    $arrProjectID[] = $value; //Указатель на распределяемую память, где хранятся модули
                    break;
                }
            }
        }

        $list = new listUnits();
        foreach ($arrProjectID as $keyP => $valueP) {
            try {
                $sm = self::getInstance($valueP);
            } catch (shareMemoryInitUnitException $e) {
                return new listUnits();
            }
            $unitsID = $sm->get(sharedMemory::KEY_UNIT_ID); //массив с key на модули
            foreach ($unitsID as $key => $value) {
                $unit = $sm->get($value);
                $disabled = $unit->checkDeviceDisabled();

                if (is_null($disabled)) {
                    continue;
                }
                elseif (!is_null($deviceDisables)){
                    if ($disabled!=$deviceDisables) {
                        continue;
                    }
                }

                if (is_null($unitType)) {  // если нет отбора, то добавляем все
                    $list->append($unit);
                }
                else {
                    //т.к. в одном сегменте могут храниться несколько типов модулей, то еще проверяем тип
                    if ($unit->getType() == $unitType) {
                        $list->append($unit);
                    }
                }
            }
        }

        return $list;
    }

    static public function getUnitLabel($label) {

        try {
            $sm = self::getInstance(sharedMemory::PROJECT_LETTER_KEY);
        } catch (shareMemoryInitUnitException $e) {
            return null;
        }

        $unitsLabels = $sm->get(sharedMemory::KEY_LABEL_MODULE);
        if (array_key_exists($label, $unitsLabels)) {
            $idModule = $unitsLabels[$label]['id_module'];
            $projectID = $unitsLabels[$label]['project_id'];
        }
        else {
            return null;
        }

        if (is_null($idModule) || is_null($projectID)) {
            return null;
        }

        try {
            $sm = self::getInstance($projectID);
        } catch (shareMemoryInitUnitException $e) {
            return null;
        }

        return $sm->get($idModule);

    }

    static public function getUnitStatusTopic($topic) {

        $result = array();

        try {
            $sm = self::getInstance(sharedMemory::PROJECT_LETTER_KEY);
        } catch (shareMemoryInitUnitException $e) {
            return $result;
        }

        $topic = trim($topic);

        $listUnitsMQQTTopicStatus = $sm->get(sharedMemory::KEY_MQQT_STATUS_TOPIC);
        foreach ($listUnitsMQQTTopicStatus as $key => $value) {
            if ($topic == $value) {
                $result[] = $key;
            }
        }

        return $result;

    }

    static public function getUnitID($id) {

        try {
            $sm = self::getInstance(sharedMemory::PROJECT_LETTER_KEY);
        } catch (shareMemoryInitUnitException $e) {
            return null;
        }

        $unitsID = $sm->get(sharedMemory::KEY_ID_MODULE);
        if (array_key_exists($id, $unitsID)) {
            $idModule = $id;
            $projectID = $unitsID[$id];
        }
        else {
            return null;
        }

        if (is_null($idModule) || is_null($projectID)) {
            return null;
        }

        try {
            $sm = self::getInstance($projectID);
        } catch (shareMemoryInitUnitException $e) {
            return null;
        }

        return $sm->get($idModule);

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

        //Получаем массив с указателями на сегменты распределяеммой памяти, ключ - тип модуля
        return $sm->get($key);

    }

}

