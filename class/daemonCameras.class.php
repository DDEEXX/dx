<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.11.20
 * Time: 17:16
 */

declare(ticks=1);

require_once(dirname(__FILE__) . '/lists.class.php');
require_once(dirname(__FILE__) . "/sqlDataBase.class.php");
require_once(dirname(__FILE__) . "/daemon.class.php");

class  daemonCameras extends daemon {
    const NAME_PID_FILE = 'cameras.pid';
    protected $stop_server = FALSE;
    protected $namePidFile;     //Полный путь до pid файла
    protected $cameraImagePath; //Путь до каталога в котором экспортирются изображения с камер

    public function __construct($dirPidFile, $cameraImagePath) {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
        $this->$cameraImagePath = $cameraImagePath;
    }

    public function run() {
        // Пока $stop_server не установится в TRUE, гоняем бесконечный цикл
//        while (!$this->stop_server) {
//
//
//
//        }

        $sel = new selectOption();
        $sel->set('Disabled', 0);

        $cameras = managerCameras::getListCameras($sel);
//        foreach ($cameras as $tekCamera) {
//
//        }
    }

}

class managerCameras
{
    /**
     * Получить камеры как объекты в виде массива
     * @param Iterator|null $sel
     * @return listCameras
     */
    public static function getListCameras(Iterator $sel = null)
    {
        $list = new listCameras();

        $arr = DB::getListCameras($sel);

        foreach ($arr as $value) {
            $Unit = new camera($value);
            $list->append($Unit);
        }
        return $list;
    }


}

class camera
{
    protected $id;
    protected $monitor;
    protected $title;

    public function __construct(array $options)
    {
        $this->id = $options['ID'];
        $this->monitor = $options['Monitor'];
        $this->title = $options['Title'];
    }



}