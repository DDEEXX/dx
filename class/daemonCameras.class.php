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
    const INTERVAL = 10; //Интервал опроса камер в секундах
    const INTERVAL_UPDATE_CAMERAS = 60; //Количество опросов после которого список камер обновиться
    protected $stop_server = FALSE;
    protected $namePidFile;     //Полный путь до pid файла

    public function __construct($dirPidFile) {
        parent::__construct($dirPidFile, self::NAME_PID_FILE);
    }

    public function run() {

        $sel = new selectOption();
        $sel->set('Disabled', 0);
        $cameras = managerCameras::getListCameras($sel);

        $start = microtime(true);
        for ($i = 0; true; ++$i) {
            if ($this->stop_server) {
                break;
            }

            if ($i%self::INTERVAL_UPDATE_CAMERAS == 0) {
                $cameras = managerCameras::getListCameras($sel);
            }

            foreach ($cameras as $camera) {
                $camera->updateFrame();
            }

            $this_iteration_start = $start + self::INTERVAL * $i;
            $next_iteration_start = $this_iteration_start + self::INTERVAL;
            time_sleep_until($next_iteration_start);
        }
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

    public function updateFrame() {

        $command = "cd ".dirname(__FILE__)."/../cam2; zmu -m 1 -i -v > /dev/null";

        exec($command);

    }

}