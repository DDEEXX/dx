<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 26.03.18
 * Time: 21:32
 */

interface loggerTypeMessage
{
    const NOTICE = '[NOTICE]';
    const WARNING = '[WARNING]';
    const ERROR = '[ERROR]';
    const FATAL = '[FATAL]';
    const ERRSTATUS = '[ERR STATUS]';
}

interface loggerName
{
    const DEFAULTLOGGER = "default";
    const ERROR = "error";
    const ACCESS = "access";
    const DEBUG = "debug";
    const MQTT = "mqtt";
}

class logger
{
    const COUNTLOGFILE = 200;
    protected $timeFormat = 'd.m.Y - H:i:s';

    protected static $PATH = 'logs';
    protected static $loggers = array();
    protected $fp;
    protected $nameFile;

    /**
     * logger constructor.
     * @param $name
     * @throws Exception
     */
    private function __construct($name)
    {
        if (!$this->fp = fopen(dirname(__FILE__).'/../'.self::$PATH . '/' . $name . '.log', 'a+')) {
            throw new Exception('Could not open file ' . self::$PATH . '/' . $name . '.log');
        }
        $this->nameFile = $name;
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        if (!fclose($this->fp)) {
            throw new Exception('Could not close file');
        }
    }

    static public function getLogger($name = loggerName::DEFAULTLOGGER)
    {
        //Если имя не задано или не подходит, то по умолчанию
        if (empty($name) || !is_string($name) || !preg_match("/^([_a-z0-9A-Z]+)$/i", $name)) {
            $name = loggerName::DEFAULTLOGGER;
        }
        if (!isset(self::$loggers[$name])) {
            try {
                self::$loggers[$name] = new logger($name);
            } catch (Exception $e) {
                error_log('Ошибка при создании логгера', 0);
                return null;
            }
        }
        return self::$loggers[$name];
    }

    public function log($message, $messageType = loggerTypeMessage::NOTICE)
    {
        if (!is_string($message)) {
            $message = "[!!!] Не возможно вывести сообщение в лог. Сообщение не строкового типа";
        }
        if ($messageType != loggerTypeMessage::NOTICE &&
            $messageType != loggerTypeMessage::WARNING &&
            $messageType != loggerTypeMessage::ERROR &&
            $messageType != loggerTypeMessage::FATAL) {
            $messageType = loggerTypeMessage::ERRSTATUS;
        }
        $this->writeToLogFile('' . date($this->timeFormat) . ';' . $messageType . ';' . $message);
    }

    private function writeToLogFile($message)
    {
        flock($this->fp, LOCK_EX);
        fwrite($this->fp, $message . PHP_EOL);
        flock($this->fp, LOCK_UN);
    }

    /**
     * Записать сообщение в файл лога
     * @param $message
     * @param string $messageType
     * @param string $name
     */
    public static function writeLog($message,
                                    $messageType = loggerTypeMessage::NOTICE,
                                    $name = loggerName::DEFAULTLOGGER)
    {
        $l = self::getLogger($name);
        if (!is_null($l)) {
            $l->log($message, $messageType);
        }
        unset($l);
    }

    /**
     * Читает лог файл и возвращает результат в виде массива, последние события
     * @param string $name
     * @return array
     */
    public static function readLog($name = loggerName::DEFAULTLOGGER)
    {
        $result = array();
        $l = self::getLogger($name);
        if (!is_null($l)) {
            $result = $l->readLogFile();
        }
        unset($l);
        return $result;
    }

    private function readLogFile()
    {
        $result = array();
        $file_path = self::$PATH . '/' . $this->nameFile . '.log'; // путь к лог файлу
        if (is_writable($file_path)) {
            $file = file($file_path, FILE_IGNORE_NEW_LINES);
            for ($i = count($file) - 1; $i >= 0; $i--) {
                $current = explode(';', $file[$i]);
                $result[$i]['date'] = $current[0];
                $result[$i]['type'] = $current[1];
                $result[$i]['message'] = $current[2];
                if (count($result) === self::COUNTLOGFILE)
                    break;
            }
            $result = array_reverse($result);
        }
        else {
            $result[] = 'ошибка при чтении файла лога';
        }
        return $result;
    }

}
