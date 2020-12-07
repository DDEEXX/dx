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
    const POWER_KEY         = 3; //Силовой ключ (коммутирует высокое наряжение)
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
    const POWER_KEY         = 3; //Силовой ключ (коммутирует высокое наряжение)
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

interface statusKey {
    const MOVE      = 'move';
    const HEAD      = 'head';
    const OUTSIDE   = 'outside';
    const OFF       = 'off';
}

interface modeUnit {
    const OFF  = 0;
    const ON   = 1;
    const AUTO = 2;
}