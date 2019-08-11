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
}

class logger
{
    protected $timeFormat = 'd.m.Y - H:i:s';

    protected static $PATH = 'logs';
    protected static $loggers = array();
    protected $fp;

    /**
     * logger constructor.
     * @param $name
     * @throws Exception
     */
    private function __construct($name)
    {
        if (!$this->fp = fopen(self::$PATH . '/' . $name . '.log', 'a+')) {
            throw new Exception('Could not open file ' . self::$PATH . '/' . $name . '.log');
        };
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
        $this->writeToLogFile('[' . date($this->timeFormat) . '] ' . $messageType . ' - ' . $message);
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

}