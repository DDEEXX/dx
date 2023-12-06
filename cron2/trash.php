<?php
require_once(dirname(__FILE__) . '/../class/sqlDataBase.class.php');

//1. удаление историю записей старше 1 месяца
    $query = 'DELETE FROM tvalue_1 WHERE Date < DATE_SUB(NOW(), INTERVAL 3 MONTH)';

try {
    $con = sqlDataBase::Connect();
    $result = queryDataBase::execute($con, $query);
    if (!$result) {
        logger::writeLog('Ошибка при записи в базу данных (writeValue)',
            loggerTypeMessage::ERROR, loggerName::ERROR);
    }
} catch (connectDBException $e) {
    logger::writeLog('Ошибка при подключении к базе данных',
        loggerTypeMessage::ERROR, loggerName::ERROR);
} catch (querySelectDBException $e) {
    logger::writeLog('Ошибка при добавлении данных в базу данных. '.$e->getMessage().' '.$e->getTraceAsString(),
        loggerTypeMessage::ERROR, loggerName::ERROR);
}

//1. удаление историю лога отопления старше недели
$query = 'DELETE FROM tvalue_1 WHERE Date < DATE_SUB(NOW(), INTERVAL 1 WEEK)';

try {
    $con = sqlDataBase::Connect();
    $result = queryDataBase::execute($con, $query);
    if (!$result) {
        logger::writeLog('Ошибка при записи в базу данных (writeValue)',
            loggerTypeMessage::ERROR, loggerName::ERROR);
    }
} catch (connectDBException $e) {
    logger::writeLog('Ошибка при подключении к базе данных',
        loggerTypeMessage::ERROR, loggerName::ERROR);
} catch (querySelectDBException $e) {
    logger::writeLog('Ошибка при добавлении данных в базу данных. '.$e->getMessage().' '.$e->getTraceAsString(),
        loggerTypeMessage::ERROR, loggerName::ERROR);
}

unset($con);