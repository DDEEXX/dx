<?php

interface iAliceFormatter
{
    function convert($value);
}

//Входящие сообщение от Алисы 0|1 в сообщение dxhome "0"|"1"
class _AliseFormatOn_0 implements iAliceFormatter
{
    function convert($value)
    {
        function convert($value)
        {
            $data['value'] = $value == '1' ? 1 : 0;
            $data['status'] = statusKey::ALICE;
            return json_encode($data);
        }
    }
}

//Входящие сообщение от Алисы 0|1 в сообщение dxhome "OFF"|"ON"
class _AliseFormatOn_1 implements iAliceFormatter
{
    function convert($value)
    {
        $data['value'] = $value == '1' ? 'on' : 'off';
        $data['status'] = statusKey::ALICE;
        return json_encode($data);
    }
}

//Входящие сообщение от Алисы 0|1 в сообщение dxhome pulse
class _AliseFormatOn_3 implements iAliceFormatter
{
    function convert($value)
    {
        $data['value'] = 'pulse';
        $data['status'] = statusKey::ALICE;
        return json_encode($data);
    }
}

//Входящие сообщение от Алисы 0..100 в сообщение dxhome 0..9 (0->8, 8->9)
class _AliseFormatBrightness_0 implements iAliceFormatter
{
    function convert($value)
    {
        if (!is_numeric($value)) $data['value'] = '9';
        else {
            $numValue = (int)(round((int)($value)*8/100) );
            if ($numValue == 0) $data['value'] = '8';
            elseif ($numValue == 8) $data['value'] = '9';
            else $data['value'] = strval($numValue);
        }
        $data['status'] = statusKey::ALICE;
        return json_encode($data);
    }
}
