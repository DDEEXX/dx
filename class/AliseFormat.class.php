<?php

interface iAliceFormatter {
    function convert($value);
}

//Входящие сообщение от Алисы 0|1 в сообщение dxhome "off"|"on"
class _AliseFormatOn_0 implements iAliceFormatter
{
    function convert($value)
    {
        function convert($value)
        {
            return $value == '1' ? 'on' : 'off';
        }
    }
}

//Входящие сообщение от Алисы 0|1 в сообщение dxhome {"state": "OFF"}|{"state": "ON"}
class _AliseFormatOn_1 implements iAliceFormatter
{
    function convert($value)
    {
        return $value == '1' ? '{"state": "ON"}' : '{"state": "OFF"}';
    }
}