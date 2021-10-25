<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 24.11.20
 * Time: 14:34
 */

require_once(dirname(__FILE__) . "/logger.class.php");

declare(ticks = 1);

interface idaemon
{
    public function run();

    public function isDaemonActive();

    public function putPitFile($pid);
}

class daemon implements idaemon
{

    protected $stop_server = FALSE;
    protected $namePidFile;     //Полный путь до pid файла

    public function __construct($dirPidFile, $namePidFile) {
        $this->namePidFile =  $dirPidFile.'/'.$namePidFile;
        pcntl_signal(SIGTERM, array($this, "childSignalHandler"));
    }

    /** @noinspection SpellCheckingInspection
     * @noinspection PhpUnusedParameterInspection
     */
    public function childSignalHandler($signo, $pid = null, $status = null) {
        logger::writeLog('SIGNAL '.$signo, loggerTypeMessage::WARNING, loggerName::DEBUG);
        switch($signo) {
            case SIGTERM:
                // При получении сигнала завершения работы устанавливаем флаг
                $this->stop_server = true;
                break;
            default:
                // все остальные сигналы
        }
    }

    public function run() {
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

    public function putPitFile($pid) {
        file_put_contents($this->namePidFile, $pid);
    }

}