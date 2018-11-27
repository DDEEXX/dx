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
    const ETHERNET      = 2;
    const CUBIEBOARD    = 3;
}

interface typeDevice{
    const NONE              = 0;
    const TEMPERATURE       = 1; //Датчик температуры
    const LABEL             = 2; //Метка
    const POWER_KEY         = 3; //Силовой ключ (коммутирует высокое наряжение)
    const KEY_IN            = 4; //Входящий ключ (сухой контакт и т.д.)
    const KEY_OUT           = 5; //Выходной ключ - коммутирует маленькие токи
    const VOLTAGE           = 6; //Датчик наличия напряжения
}
