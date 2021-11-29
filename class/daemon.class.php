<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24.11.20
 * Time: 14:34
 */

interface idaemon
{
    public function run();

    public function isDaemonActive();

}

class daemon implements idaemon
{

    private $stop_server = false;
    private $namePidFile;     //Полный путь до pid файла

    public function __construct($dirPidFile, $namePidFile) {
        $this->namePidFile =  $dirPidFile.'/'.$namePidFile;
        pcntl_signal(SIGTERM, [$this, 'childSignalHandler']);
    }

    protected function childSignalHandler($signo) {
        switch($signo) {
            case SIGTERM:
                // При получении сигнала завершения работы устанавливаем флаг
                $this->stop_server = true;
                break;
            default:
                // все остальные сигналы игнорируем
        }
    }

    public function run() {
        $this->putPitFile();
    }

    protected function stopServer() {
        return $this->stop_server;
    }

    public function isDaemonActive() {
        if( is_file($this->namePidFile) ) {
            $pid = file_get_contents($this->namePidFile);
            //проверяем на наличие процесса
            if(posix_kill($pid,0)) {
                //демон уже запущен
                return 1;
            } else { //pid-файл есть, но процесса нет
                if(!unlink($this->namePidFile)) {//не могу уничтожить pid-файл, ошибка
                    return -1;
                }
            }
        }
        return 0;
    }

    protected function putPitFile() {
        $pid = getmypid();
        file_put_contents($this->namePidFile, $pid);
    }

}