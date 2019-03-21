<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 26.03.18
 * Time: 21:32
 */

class FileLoggerException extends Exception
{
}

class logger
{

    const DEFAULTLOGGER = "default";

    const NOTICE = '[NOTICE]';
    const WARNING = '[WARNING]';
    const ERROR = '[ERROR]';
    const FATAL = '[FATAL]';
    const ERRSTATUS = '[ERR STATUS]';

    protected $timeFormat = 'd.m.Y - H:i:s';

    protected static $PATH = 'logs';
    protected static $loggers = array();
    protected $fp;

    private function __construct($name)
    {
        if (!$this->fp = fopen(self::$PATH . '/' . $name . '.log', 'a+')) {
            throw new FileLoggerException('Could not open file ' . self::$PATH . '/' . $name . '.log');
        };
    }

    public function __destruct()
    {
        if (!fclose($this->fp)) {
            throw new FileLoggerException('Could not close file');
        }
    }

    static public function getLogger($name = logger::DEFAULTLOGGER)
    {
        //Если имя не задано или не подходит, то по умолчанию
        if (empty($name) || !is_string($name) || !preg_match("/^([_a-z0-9A-Z]+)$/i", $name)) {
            $name = self::DEFAULTLOGGER;
        }
        if (!isset(self::$loggers[$name])) {
            try {
                self::$loggers[$name] = new logger($name);
            } catch (FileLoggerException $e) {
                error_log('Ошибка при создании логгера', 0);
            }
        }
        return self::$loggers[$name];
    }

    public function log($message, $messageType = logger::NOTICE)
    {
        if (!is_string($message)) {
            $message = "[!!!] Не возможно вывести сообщение в лог. Сообщение не строкового типа";
        }
        if ($messageType != logger::NOTICE &&
            $messageType != logger::WARNING &&
            $messageType != logger::ERROR &&
            $messageType != logger::FATAL) {
            $messageType = logger::ERRSTATUS;
        }
        $this->writeToLogFile('[' . date($this->timeFormat) . '] ' . $messageType . ' - ' . $message);
    }

    private function writeToLogFile($message)
    {
        flock($this->fp, LOCK_EX);
        fwrite($this->fp, $message . PHP_EOL);
        flock($this->fp, LOCK_UN);
    }

    public static function writeLog($message, $messageType = logger::NOTICE, $name = logger::DEFAULTLOGGER)
    {
        $l = self::getLogger($name);
        $l->log($message, $messageType);
        unset($l);
    }

}