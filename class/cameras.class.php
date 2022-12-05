<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.11.20
 * Time: 17:16
 */

require_once(dirname(__FILE__) . '/lists.class.php');
require_once(dirname(__FILE__) . '/sqlDataBase.class.php');
require_once(dirname(__FILE__) . '/logger.class.php');

interface iCamera
{
    const MONTH = [1=>'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь' ];
    const DIR_ARCHIVE = 'cam2/archive';
    function checkCameraDir();
    function getArchiveImageDirStructureYearMonth();
    function getArchiveImageDays($year, $month);
    function getArchiveImageShots($year, $month, $day);
    function getArchiveImageShotFullFileName($nameFileArchive);
    function getArchiveTimelapse();
    function getArchiveTimelapseLocalFileName($nameFileArchive);
}

class managerCameras
{
    public static function runJobCreateArchive()
    {

        logger::writeLog('Старт архивирования записей с камер', loggerTypeMessage::NOTICE, loggerName::CAMERAS);

        $select = new selectOption();
        $select->set('Disabled', 0);
        $listCameras = self::getListCameras($select);

        foreach ($listCameras as $camera) {
            //0. Проверяем доступность каталогов, для работы с камерой
            if (!$camera->checkCameraDir()) {
                logger::writeLog($camera->getInformation . ' Каталоги для работы с камерами не доступны', loggerTypeMessage::ERROR, loggerName::CAMERAS);
                break;
            }

            //1. Перемещаем файлы timelapse.avi и с датой меньшей чем сегодня в директорию камеры timelapse
            $camera->moveTimelapseFileInArchive();

            //2. Оставляем в каталоге timelapse не больше 30 самых последних файлов
            $camera->deleteTimelapseFileInArchive();

            //3. Объединяем все видео файлы за день в один и удаляем исходные файлы.
            $camera->concatenationVideoFile();

            //4. Оставляем в каталоге video не больше 90 самых последних файлов
            $camera->deleteVideoFileInArchive();

            //5. Перемещаем стоп кадры в архив, сохраняем только один кадр за час, все исходные кадры удаляем
            $camera->moveImageFileInArchive();

        }

    }

    /**
     * Получить камеры как объекты в виде массива
     * @param Iterator|null $sel
     * @return listCameras
     */
    private static function getListCameras(Iterator $sel = null)
    {
        $list = new listCameras();

        $arr = DB::getListCameras($sel);

        foreach ($arr as $value) {
            $Unit = new camera($value);
            $list->append($Unit);
        }
        return $list;
    }

    /** Получить камеру по ее ID
     * @param $IdCamera - id камеры
     * @return iCamera|null - камера или null если камера не найден
     */
    public static function getCamera($IdCamera)
    {
        $select = new selectOption();
        $select->set('ID', $IdCamera);
        $result = self::getListCameras($select);
        if (count($result) != 1) {
            return null;
        } else {
            return $result[0];
        }
    }
}

class camera implements iCamera
{
    protected $id;
    protected $title;
    protected $targetDir = ''; //каталог, куда камера сохраняет все файлы
    protected $archiveDir = ''; //каталог архива камеры
    protected $archiveDirLocal = ''; //каталог архива камеры относительно сайта
    private $extensionVideo = 'avi'; //расширение видео файлов
    private $extensionVideoConvert = 'mp4'; //расширение видео файлов
    private $extensionImage = 'jpg'; //расширение изображений
    private $maskTimelapseFiles = '*-timelapse.avi'; //маска для поиска timelapse файлов
    private $maskTimelapseFiles_ = '-timelapse.avi'; //маска для проверки является ли файл timelapse файлом
    private $timelapseDir = 'timelapse'; //наименование каталога для хранения timelapse файлов
    private $countTimelapseFiles = 30; //максимальное количество timelapse файлов в архиве
    private $videoDir = 'video'; //наименование каталога для хранения склеенных video файлов с движением
    private $countVideoFiles = 90; //максимальное количество видео файлов в архиве
    private $nameVideoFiles = '-camera';
    private $imageDir = 'image'; //наименование каталога для хранения стоп кадров видео с движением
    private $permissions = 0755; //права для создаваемых каталогов (владелец - запись/чтение, остальные - чтение)
    private $wwwGroup = 'www-data'; //группа назначаемая на новые каталоги файлы
    private $wwwOwner = 'www-data'; //пользователь назначаемый на новые каталоги и файлы
    private $listVideoFiles = 'list_cam.txt'; //временный файл для склейки видео

    public function __construct(array $options)
    {
        $this->id = $options['ID'];
        $this->title = $options['Title'];
        if (is_dir($options['Target'])) {
            $this->targetDir = $options['Target'];
        }
        $archiveDir = $options['Archive'];
        $this->archiveDirLocal = iCamera::DIR_ARCHIVE . ($archiveDir[0] === DIRECTORY_SEPARATOR ? $archiveDir : (DIRECTORY_SEPARATOR . $archiveDir));
        $this->archiveDir = __DIR__ .'/../'.$this->archiveDirLocal;
    }

    /**
     * Проверка доступности каталогов для работы с камерой
     * @return bool
     */
    public function checkCameraDir()
    {
        $result = $this->checkDir($this->archiveDir);
        if (!is_dir($this->targetDir)) {
            $result = false;
        }
        return $result;
    }

    /**
     * Перемещает все timelapse файлы с датой создания меньшей чем $date в каталог $timelapseDir
     * @return void
     */
    public function moveTimelapseFileInArchive()
    {
        if (!$this->checkTimelapseDir()) {
            logger::writeLog($this->getInformation() . ' Отсутствует каталог для timelapse файлов.',
                loggerTypeMessage::ERROR, loggerName::CAMERAS);
            return;
        }
        $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        foreach (glob($this->targetDir . '/' . $this->maskTimelapseFiles) as $filename) {
            if (file_exists($filename) && is_file($filename)) {
                $time = filemtime($filename);
                if ($time > $today) {
                    continue;
                }
                $newFilename = pathinfo($filename,  PATHINFO_FILENAME);
                $newName = $this->getTimelapseDir() . '/' . $newFilename. '.' . $this->extensionVideoConvert;
                try {
                    $command = sprintf('ffmpeg -i %s -c:v libx264 -c:a copy -y %s', $filename, $newName);
                    $output = NULL;
                    $result_code = NULL;
                    exec($command, $output, $result_code);
                    if ($result_code != 0) {
                        logger::writeLog($this->getInformation() . ' Ошибка при перемещении файла ' . $filename . ' в ' . $newName,
                            loggerTypeMessage::ERROR, loggerName::CAMERAS);
                    }
                    else {
                        unlink($filename);
                        chgrp($newName, $this->wwwGroup);
                        chown($newName, $this->wwwOwner);
                        chmod($newName, $this->permissions);
                    }
                } catch (Exception $e) {
                    logger::writeLog($this->getInformation() . ' Ошибка при перемещении файла ' . $filename . ' в ' . $newName . '. ' . $e->getMessage(),
                        loggerTypeMessage::ERROR, loggerName::CAMERAS);
                }
            } else {
                logger::writeLog($this->getInformation() . ' Обращение к отсутствующему файлу ' . $filename,
                    loggerTypeMessage::ERROR, loggerName::CAMERAS);
            }
        }
    }

    /**
     * Удаляет старые timelapse файлы из архива
     * @return void
     */
    public function deleteTimelapseFileInArchive()
    {
        $mask = $this->getTimelapseDir() . '/' . $this->maskTimelapseFiles;
        $this->deleteFile($mask, $this->countTimelapseFiles);
    }

    /**
     * Объединяет видео файлы за день в один видео файл, исходные файлы удаляются.
     * За один вызов обрабатывается не более 10 дней, начиная с самого раннего
     * @return void
     */
    public function concatenationVideoFile()
    {

        if (!$this->checkVideoDir()) {
            logger::writeLog($this->getInformation() . ' Отсутствует каталог для video файлов.',
                loggerTypeMessage::ERROR, loggerName::CAMERAS);
            return;
        }

        //За раз обрабатываем не более 10 дат.
        for ($i = 0; $i < 10; $i++) {

            //Ищем avi файл с наименьшей датой, и что бы эта дата была меньше чем сегодня
            $tekDate = $this->getOldestDataVideoFile();
            if (is_null($tekDate)) {
                break;
            }

            //Получаем все видео файлы за день
            $videoFiles = $this->getVideoFilesDay($tekDate);
            if (count($videoFiles) > 0) {
                $nameConcatVideoFile = $this->getVideoDir() . '/' . date('Ymd', $tekDate) . $this->nameVideoFiles . $this->getId() . '.' . $this->extensionVideoConvert;
                if ($this->concatenationVideoFileInArchive($nameConcatVideoFile, $videoFiles)) {
                    //Удаляем исходные файлы
                    $this->deleteFiles($videoFiles);
                }
            }
        }
    }

    /**
     * Удаляет старые видео файлы из архива
     * @return void
     */
    public function deleteVideoFileInArchive()
    {
        $mask = $this->getVideoDir() . '/*' . $this->nameVideoFiles . $this->getId() . '.' . $this->extensionVideo;
        $this->deleteFile($mask, $this->countVideoFiles);
    }

    public function moveImageFileInArchive()
    {
        //За раз обрабатываем не более 10 дат.
        for ($i = 0; $i < 10; $i++) {

            //Ищем файл изображений, с наименьшей датой, и что бы эта дата была меньше чем сегодня
            $tekDate = $this->getOldestDataImageFile();
            if (is_null($tekDate)) {
                return;
            }

            if (!$this->checkImageDataDir($tekDate, $tekImageDir)) {
                logger::writeLog($this->getInformation() . ' Недоступен каталог для изображений на дату ' . date('Y-m-d', $tekDate) . '.',
                    loggerTypeMessage::ERROR, loggerName::CAMERAS);
                return;
            }

            //Получаем все видео файлы за день
            $imageFiles = $this->getImageFilesDay($tekDate);
            if (count($imageFiles) > 0) {
                if ($this->moveListImageFileInArchive($tekImageDir, $imageFiles)) {
                    //Удаляем исходные файлы
                    clearstatcache();
                    $imageFiles = $this->getImageFilesDay($tekDate);
                    $this->deleteFiles($imageFiles);
                }
            }
        }
    }

    /**
     * Перемещает файл (один за час) из списка в каталог
     * @param $imageDir - каталог в который перемещаются файлы
     * @param $imageFiles - список файлов
     * @return bool
     */
    private function moveListImageFileInArchive($imageDir, $imageFiles)
    {
        $tekHour = -1;
        foreach ($imageFiles as $nameFile => $timeFile) {
            $hourFile = (int)date('G', $timeFile);
            if ($hourFile > $tekHour) {
                $newName = $imageDir . '/' . pathinfo($nameFile, PATHINFO_BASENAME);
                try {
                    if (rename($nameFile, $newName)) {
                        chgrp($newName, $this->wwwGroup);
                        chown($newName, $this->wwwOwner);
                        chmod($newName, $this->permissions);
                    }
                    else {
                        logger::writeLog($this->getInformation() . ' Ошибка при перемещении файла ' . $nameFile . ' в ' . $newName . '. ',
                            loggerTypeMessage::ERROR, loggerName::CAMERAS);
                        return false;
                    }
                } catch (Exception $e) {
                    logger::writeLog($this->getInformation() . ' Ошибка при перемещении файла ' . $nameFile . ' в ' . $newName . '. ' . $e->getMessage(),
                        loggerTypeMessage::ERROR, loggerName::CAMERAS);
                    return false;
                }
                $tekHour = $hourFile;
            }
        }
        return true;
    }

    /**
     * Удаляет файлы по маске. Удаление идет, пока останется не более count файлов
     * @param $mask - маска для поиска файлов
     * @param $count - максимальное количество файлов, которое должно остаться
     * @return void
     */
    private function deleteFile($mask, $count)
    {
        $files = [];
        foreach (glob($mask) as $filename) {
            if (file_exists($filename) && is_file($filename)) {
                $time = filemtime($filename);
                $files[$filename] = $time;
            }
        }
        asort($files, SORT_NUMERIC);
        $countFiles = count($files);
        foreach ($files as $key => $value) {
            if ($countFiles <= $count) {
                break;
            }
            unlink($key);
            $countFiles--;
        }
    }

    /**
     * Назначает каталогу группу $wwwGroup
     * @param $nameDir - имя каталога
     * @return bool - результат выполнения
     */
    private function changeGroupUserDir($nameDir)
    {
        if (is_dir($nameDir)) {
            try {
                $idGroup = filegroup($nameDir);
                $groupInfo = posix_getgrgid($idGroup);
                if (strcasecmp($this->wwwGroup, $groupInfo['name']) !== 0) {
                    try {
                        if (!chgrp($nameDir, $this->wwwGroup)) {
                            logger::writeLog($this->getInformation() . ' Ошибка при назначении группы ' . $this->wwwGroup . ' каталогу ' . $nameDir . '. ',
                                loggerTypeMessage::ERROR, loggerName::CAMERAS);
                            return false;
                        }
                    } catch (Exception $e) {
                        logger::writeLog($this->getInformation() . '. ' . $e->getMessage(),
                            loggerTypeMessage::ERROR, loggerName::CAMERAS);
                        return false;
                    }
                }
            } catch (Exception $e) {
                logger::writeLog($this->getInformation() . '. ' . $e->getMessage(),
                    loggerTypeMessage::ERROR, loggerName::CAMERAS);
                return false;
            }
            try {
                $idOwner = fileowner($nameDir);
                $ownerInfo = posix_getpwuid($idOwner);
                if (strcasecmp($this->wwwOwner, $ownerInfo['name']) !== 0) {
                    try {
                        if (!chown($nameDir, $this->wwwOwner)) {
                            logger::writeLog($this->getInformation() . ' Ошибка при назначении владельца ' . $this->wwwGroup . ' каталогу ' . $nameDir . '. ',
                                loggerTypeMessage::ERROR, loggerName::CAMERAS);
                            return false;
                        }
                    } catch (Exception $e) {
                        logger::writeLog($this->getInformation() . '. ' . $e->getMessage(),
                            loggerTypeMessage::ERROR, loggerName::CAMERAS);
                        return false;
                    }
                }
            } catch (Exception $e) {
                logger::writeLog($this->getInformation() . '. ' . $e->getMessage(),
                    loggerTypeMessage::ERROR, loggerName::CAMERAS);
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Проверяет существование каталога, при необходимости создает его, и также проверяет группу этого каталога
     * @param $dir - полный путь до каталога
     * @return bool - результат проверки
     */
    private function checkDir($dir)
    {
        if (is_dir($dir)) {
            $result = $this->changeGroupUserDir($dir);
        } else {
            try {
                if (mkdir($dir, $this->permissions, true)) {
                    $result = $this->changeGroupUserDir($dir);
                } else {
                    logger::writeLog($this->getInformation() . ' Ошибка при создании каталога ' . $dir . '. ',
                        loggerTypeMessage::ERROR, loggerName::CAMERAS);
                    $result = false;
                }
            } catch (Exception $e) {
                logger::writeLog($this->getInformation() . ' Ошибка при создании каталога ' . $dir . '. ' . $e->getMessage(),
                    loggerTypeMessage::ERROR, loggerName::CAMERAS);
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Получить информацию о камере
     * @return string
     */
    private function getInformation()
    {
        return 'cam id: ' . $this->getId() . ' title: ' . $this->title;
    }

    /**
     * Возвращает начало дня по времени самого раннего видео файла (файла с расширением $extensionVideo)
     * @return int|null
     */
    private function getOldestDataVideoFile()
    {
        return $this->getOldestFileTargetDir($this->extensionVideo);
    }

    /**
     * Возвращает начало дня по времени самого раннего видео файла (файла с расширением $extensionVideo)
     * @return int|null
     */
    private function getOldestDataImageFile()
    {
        return $this->getOldestFileTargetDir($this->extensionImage);
    }

    /**
     * Возвращает начало дня по времени самого раннего файла (файла с расширением $fileExtension)
     * @param $fileExtension - расширение файлов
     * @return int|null
     */
    private function getOldestFileTargetDir($fileExtension)
    {
        $directory = $this->targetDir;
        $smallest_time = INF;
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                $fullName = $directory . '/' . $file;
                if (is_file($fullName)) {
                    $extension = pathinfo($fullName, PATHINFO_EXTENSION);
                    if (strcasecmp($extension, $fileExtension) == 0) { //проверка расширения
                        $time = filemtime($fullName);
                        if ($time < $smallest_time) {
                            $smallest_time = $time;
                        }
                    }
                }
            }
            closedir($handle);
        } else {
            logger::writeLog($this->getInformation() . ' ошибка при открытии каталога ' . $directory,
                loggerTypeMessage::ERROR, loggerName::CAMERAS);
        }
        $result = NULL;
        if ($smallest_time != INF) {
            $timeFile = mktime(0, 0, 0,
                date('m', $smallest_time), date('d', $smallest_time), date('Y', $smallest_time));
            if ($timeFile !== false) {
                $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                if ($today !== false && $today > $timeFile) {
                    $result = $timeFile;
                }
                $result = $timeFile;
            }
        }
        return $result;
    }

    /**
     * Получить полный путь до Timelapse каталога камеры
     * @return string
     */
    private function getTimelapseDir($local = false)
    {
        if ($local) {
            return $this->archiveDirLocal . '/' . $this->timelapseDir;
        }
        return $this->archiveDir . '/' . $this->timelapseDir;
    }

    /**
     * Проверяет/создает каталог для хранения timelapse файлов
     * @return bool - результат проверки
     */
    private function checkTimelapseDir()
    {
        $timelapseDir = $this->getTimelapseDir();
        return $this->checkDir($timelapseDir);
    }

    /**
     * Получить полный путь до Video каталога камеры
     * @return string
     */
    private function getVideoDir()
    {
        return $this->archiveDir . '/' . $this->videoDir;
    }

    /**
     * Проверяет/создает каталог для хранения timelapse файлов
     * @return bool - результат проверки
     */
    private function checkVideoDir()
    {
        $videoDir = $this->getVideoDir();
        return $this->checkDir($videoDir);
    }

    /**
     * Получает список видео файлов за один день.
     * Файлы отсортированы по дате
     * @param $date - дата дня
     * @return array - список файлов: ключ - имя файла, значение - дата файла
     */
    private function getVideoFilesDay($date)
    {
        return $this->getFilesDay($date, $this->extensionVideo);
    }

    /**
     * Получает список файлов изображений за один день.
     * Файлы отсортированы по дате
     * @param $date - дата дня
     * @return array - список файлов: ключ - имя файла, значение - дата файла
     */
    private function getImageFilesDay($date)
    {
        return $this->getFilesDay($date, $this->extensionImage);
    }

    /**
     * Получает список файлов за один день.
     * @param $date - дата дня
     * @param $fileExtension - расширение искомых файлов
     * @return array - массив имен файлов, отсортированный по времени файлов
     */
    private function getFilesDay($date, $fileExtension)
    {
        $nameFiles = [];
        $dateDay = mktime(0, 0, 0, date('m', $date), date('d', $date), date('Y', $date)); //Начало дня по дате $date
        $targetDir = $this->targetDir;
        if ($handle = opendir($targetDir)) {
            while (false !== ($file = readdir($handle))) {
                $fullName = $targetDir . '/' . $file;
                if (is_file($fullName)) {
                    $extension = pathinfo($fullName, PATHINFO_EXTENSION);
                    if (strcasecmp($extension, $fileExtension) == 0) { //это видео файл
                        //проверяем для видео вдруг остался файл timelapse
                        if (strpos($file, $this->maskTimelapseFiles_) !== false) {
                            continue;
                        }
                        $timeFile = filemtime($fullName);
                        //начало дня по дате файла
                        $timeFileDay = mktime(0, 0, 0, date('m', $timeFile), date('d', $timeFile), date('Y', $timeFile));
                        if ($timeFileDay !== false && $dateDay == $timeFileDay) {
                            $nameFiles[$fullName] = $timeFile;
                        }
                    }
                }
            }
            closedir($handle);
        } else {
            logger::writeLog($this->getInformation() . ' ошибка при открытии каталога ' . $targetDir,
                loggerTypeMessage::ERROR, loggerName::CAMERAS);
        }
        asort($nameFiles, SORT_NUMERIC);
        return $nameFiles;
    }

    /**
     * Удаление файлов
     * @param $files - массив, ключ - имя файла
     * @return void
     */
    private function deleteFiles($files)
    {
        foreach ($files as $key => $value) {
            unlink($key);
        }
    }

    /**
     * Объединяет видео файлы в один
     * @param $nameArchiveVideoFile - имя файла для объединения
     * @param $videoFiles - массив с именами видео файлов, ключ - имя файла
     * @return bool - результат выполнения
     */
    private function concatenationVideoFileInArchive($nameArchiveVideoFile, $videoFiles)
    {
        if (count($videoFiles) > 0) {
            if (file_exists($nameArchiveVideoFile)) {
                if (filesize($nameArchiveVideoFile) > 0) {
                    //файл уже существует и его размер больше нуля
                    logger::writeLog($this->getInformation() . ' При объединении видео файлов, обнаружен результирующий файл ' . $nameArchiveVideoFile . '. Объединение прекращено.',
                        loggerTypeMessage::ERROR, loggerName::CAMERAS);
                    return false;
                } else {
                    unlink($nameArchiveVideoFile);
                }
            }
            //объединяем все видео файлы
            $data = '';
            foreach ($videoFiles as $key => $value) {
                $data .= "file '" . $key . "'" . PHP_EOL;
            }
            file_put_contents($this->listVideoFiles, $data);
            $command = sprintf('ffmpeg -f concat -safe 0 -i %s -c:v libx264 -c:a copy %s', $this->listVideoFiles, $nameArchiveVideoFile);
            $output = NULL;
            $result_code = NULL;
            exec($command, $output, $result_code);
            unlink($this->listVideoFiles);
            if ($result_code != 0) {
                return false;
            }
            else {
                chgrp($nameArchiveVideoFile, $this->wwwGroup);
                chown($nameArchiveVideoFile, $this->wwwOwner);
                chmod($nameArchiveVideoFile, $this->permissions);
            }
        }
        return true;
    }

    /**
     * Проверяет и при необходимости создает каталоги для хранения архивов изображений
     * @param $date - Дата дня, за который создается архив
     * @param $dayDir - в переменную возвращается имя каталога для хранения изображений на даты
     * @return bool - результат выполнения
     */
    private function checkImageDataDir($date, &$dayDir)
    {
        $Y = date('Y', $date);
        $M = date('m', $date);
        $D = date('d', $date);
        $imageDir = $this->getImageDir();
        $yearDir = $imageDir . '/' . $Y;
        $monthDir = $yearDir . '/' . $M;
        $dayDir = $monthDir . '/' . $D;

        $resultImageDir = $this->checkDir($imageDir);
        $resultYearDir = $this->checkDir($yearDir);
        $resultMonthDir = $this->checkDir($monthDir);
        $resultDayDir = $this->checkDir($dayDir);

        return $resultImageDir && $resultYearDir && $resultMonthDir && $resultDayDir;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    private function getImageDir()
    {
        return $this->archiveDir . '/' . $this->imageDir;
    }

    /** Получает структуру папок хранения архива изображений
     * @return array - индекс год, значение массив месяцев
     */
    public function getArchiveImageDirStructureYearMonth() {

        $result = [];
        $imageDir = $this->getImageDir();
        if (!is_dir($imageDir)) {
            logger::writeLog('Не удалось обратиться к архиву изображений камеры, путь='.$imageDir,
                loggerTypeMessage::ERROR,
                loggerName::CAMERAS);
            return $result;
        }
        if ($handleY = opendir($imageDir)) { //сканирование годов
            while (false !== ($fileY = readdir($handleY))) {
                $fullNameY = $imageDir . '/' . $fileY;
                if (is_dir($fullNameY)) {
                    $month = [];
                    if ($fileY == '.' || $fileY == '..') { continue; }

                    if ($handleM = opendir($fullNameY)) { //сканирование месяцев года
                        while (false !== ($fileM = readdir($handleM))) {
                            $fullNameM = $fullNameY.'/'.$fileM;
                            if ($fileM == '.' || $fileM == '..') { continue; }
                            if (is_dir($fullNameM)) {
                                $month[] = $fileM;
                            }
                        }
                    }
                    sort($month, SORT_NATURAL);
                    $result[$fileY] = $month;
                }
            }
            closedir($handleY);
        }
        krsort($result,  SORT_NATURAL );
        return $result;
    }

    public function getArchiveImageDays($year, $month) {
        $result = [];
        $path = $this->getImageDir().'/'.$year.'/'.$month;
        if (!is_dir($path)) {
            return $result;
        }
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $fullName = $path . '/' . $file;
                if (is_dir($fullName)) {
                    if ($file == '.' || $file == '..') { continue; }
                    $result[] = $file;
                }
            }
            closedir($handle);
        }
        sort($result,  SORT_NATURAL );
        return $result;
    }

    function getArchiveImageShots($year, $month, $day) {
        $result = [];
        $path = $this->getImageDir().'/'.$year.'/'.$month.'/'.$day;
        if (!is_dir($path)) {
            return $result;
        }
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $image_name = $path . '/' . $file;
                if (is_file($image_name)) {
                    $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                    if ($ext == 'jpg') {
                        $result[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        sort($result,  SORT_NATURAL );
        return $result;
    }

    function getArchiveImageShotFullFileName($nameFileArchive) {
        $fullNameFile = $this->getImageDir().'/'.$nameFileArchive;
        if (is_file($fullNameFile)) {
            return $fullNameFile;
        }
        return '';
    }

    function getArchiveTimelapse() {
        $result = [];
        $path = $this->getTimelapseDir();
        if (!is_dir($path)) {
            logger::writeLog('Не найден каталог с timelapse камеры, путь='.$path,
                loggerTypeMessage::ERROR,
                loggerName::CAMERAS);
            return $result;
        }
        if ($handle = opendir($path)) { //сканирование годов
            while (false !== ($file = readdir($handle))) {
                $fullName = $path . '/' . $file;
                if (is_file($fullName)) {
                    if (fnmatch('*-timelapse.mp4', $file)) {
                        $result[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        else {
            logger::writeLog('Не удалось прочитать содержимое каталога '.$path.
                ' при получении файлов timelapse',
                loggerTypeMessage::ERROR,
                loggerName::CAMERAS);
        }
        sort($result,  SORT_NATURAL );
        return $result;
    }

    function getArchiveTimelapseLocalFileName($nameFileArchive)
    {
       $fullNameFile = $this->getTimelapseDir(true).'/'.$nameFileArchive;
        if (is_file($fullNameFile)) {
            return $fullNameFile;
        }
        return '';
    }
}