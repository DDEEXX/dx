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
    const VOLTAGE           = 6; //Датчик наличия напряжения
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
    const VOLTAGE           = 6; //Датчик наличия напряжения
    const PRESSURE          = 7; //Датчик атмосферного давления
    const HUMIDITY          = 8; //Датчик влажности
}

interface graphType {
    const LINE = 0; //линейный график
    const BAR  = 1; //столбчатый график
}

//Каким способом включился модуль
interface statusKey {
    const MOVE      = 'move';       //от датчика движения
    const WEB       = 'web';        //через сайт или приложение
    const OFF       = 'off';        //выключен
    const UNKNOWN   = 'unknown';    //неизвестно, скорее всего через выключатель не связанный с сервером
    const DEVICE    = 'device';     //на самом модуле
}

interface modeUnit {
    const OFF  = 0;
    const ON   = 1;
    const AUTO = 2;
}

interface sharedMemory
{
    const SIZE_MEMORY_UNIT = 10000;
    const PROJECT_LETTER_KEY = 'A';
    const MEMORY_SIZE_KEY = 10000;
    const KEY_UNIT_ID = 0;
    const KEY_UNIT_TYPE = 0;
    const KEY_ID_MODULE = 1;
    const KEY_LABEL_MODULE = 2;
    const KEY_1WARE_PATH = 3;
    const KEY_1WARE_ADDRESS = 4;
}

interface modeDeviceValue {
    const IS_NULL = null;
    const GET_VALUE = 0; //можно получить значение датчика в любое время
    const LOOP_VALUE = 1; //надо постоянно слушать, данные датчик отправляет сам
}

interface testUnitCode {
    const WORKING = 0;
    const NO_CONNECTION = 1;
    const NO_DEVICE = 2;
    const DISABLED = 3;
    const ONE_WIRE_ADDRESS = 4;
}

const MQTT_CODE_SEPARATOR = ';';