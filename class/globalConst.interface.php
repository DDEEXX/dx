<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 26.11.18
 * Time: 20:15
 */

interface netDevice {
    const NONE          = 0;
    const ONE_WIRE      = 1;
    const ETHERNET_JSON = 2;
    const PIO           = 3;
    const I2C           = 4;
    const ETHERNET_MQTT = 5;
}

interface typeDevice{
    const NONE              = 0;
    const TEMPERATURE       = 1; //Датчик температуры
    const LABEL             = 2; //Метка
    const POWER_KEY         = 3; //Силовой ключ (коммутирует высокое напряжение)
    const KEY_IN            = 4; //Входящий ключ (сухой контакт и т.д.)
    const KEY_OUT           = 5; //Выходной ключ - коммутирует маленькие токи
    const PRESSURE          = 7; //Датчик атмосферного давления
    const HUMIDITY          = 8; //Датчик влажности
}

interface typeUnit{
    const NONE              = 0;
    const TEMPERATURE       = 1; //Температура
    const LABEL             = 2; //Метка
    const POWER_KEY         = 3; //Силовой ключ (коммутирует высокое напряжение)
    const KEY_IN            = 4; //Входящий ключ (сухой контакт и т.д.)
    const KEY_OUT           = 5; //Выходной ключ - коммутирует маленькие токи
    const PRESSURE          = 7; //Датчик атмосферного давления
    const HUMIDITY          = 8; //Датчик влажности
}

interface graphType {
    const LINE = 0; //линейный график
    const BAR  = 1; //столбчатый график
    const BAR_AVERAGE  = 3; //столбчатый график, средние значения, график не с 0, только изменения (для давления)
}

interface graphVariant {
    const VAR1 = 0; //график минимальный
    const VAR2 = 1; //график средний
}

//Каким способом включился модуль
interface statusKey {
    const MOVE      = 'move';       //от датчика движения
    const WEB       = 'web';        //через сайт или приложение
    const OFF       = 'off';        //выключен
    const UNKNOWN   = 'unknown';    //неизвестно, скорее всего через выключатель не связанный с сервером
    const DEVICE    = 'device';     //на самом модуле
}

/** Каким способом включился модуль - числовой код */
interface statusKeyData {

    const status = ['none'=>0,
        'move'=>1,
        'web'=>2,
        'off'=>3,
        'unknown'=>4,
        'device'=>5];

    const NONE      = 0;  //нет статуса
    const MOVE      = 1;  //от датчика движения
    const WEB       = 2;  //через сайт или приложение
    const OFF       = 3;  //выключен
    const UNKNOWN   = 4;  //неизвестно, скорее всего через выключатель не связанный с сервером
    const DEVICE    = 5;  //на самом модуле

}

interface modeUnit {
    const OFF  = 0;
    const ON   = 1;
    const AUTO = 2;
}

interface sharedMemory
{
    const SIZE_MEMORY_KEY = 10000;
    const SIZE_MEMORY_UNITS = 20000;
    const SIZE_MEMORY_DATA_DEVICE = 30000;
    const PROJECT_LETTER_KEY = 'A';
    const PROJECT_LETTER_UNITS = 'B';
    const PROJECT_LETTER_DATA_DEVICE = 'C';
    const KEY_1WARE_PATH = 0;
    const KEY_1WARE_ADDRESS = 1;
}

interface modeDeviceValue {
    const IS_NULL = null;
    const GET_VALUE = 0; //можно получить значение датчика в любое время
    const LOOP_VALUE = 1; //надо постоянно слушать, данные датчик отправляет сам
}

interface testDeviceCode {
    const WORKING = 0;
    const NO_CONNECTION = 1;
    const NO_DEVICE = 2;
    const DISABLED = 3;
    const ONE_WIRE_ADDRESS = 4;
    const ONE_WIRE_ALARM = 5;
    const IS_MQTT_DEVICE = 10;
    const NO_TEST = 11;
}

const MQTT_CODE_SEPARATOR = ';';

interface valuePrecision {
    const TEMPERATURE = 2;
}

interface formatValueDevice {
    const NO_FORMAT = 0;
    const MQTT_TEMPERATURE = 1;
    const MQTT_HUMIDITY = 2;
    const MQTT_PRESSURE = 3;
    const MQTT_KEY_IN = 4;
    const MQTT_KEY_OUT = 5;
}