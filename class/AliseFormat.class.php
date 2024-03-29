<?php

interface iAliceFormatter
{
    function convert($value);
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

//Входящие сообщение от Алисы 0|1 в сообщение dxhome "0"|"1"
class _AliseFormatOn_2 implements iAliceFormatter
{
    function convert($value)
    {
        $data['value'] = $value == '1' ? 1 : 0;
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

//Входящие сообщение от Алисы 0|1 в сообщение dxhome {"state":"on"}|{"state":"off"}
class _AliseFormatOn_4 implements iAliceFormatter
{
    function convert($value)
    {
        return $value == '1' ? '{"state":"on"}' : '{"state":"off"}';
    }
}

//Входящие сообщение от Алисы 1 в сообщение dxhome {"value":"pulse"}, остальные игнорируются
class _AliseFormatOn_5 implements iAliceFormatter
{
    function convert($value)
    {
        return $value == '1' ? '{"value":"pulse"}' : null;
    }
}

//Входящие сообщение от Алисы 0 в сообщение dxhome {"value":"pulse"}, остальные игнорируются
class _AliseFormatOn_6 implements iAliceFormatter
{
    function convert($value)
    {
        return $value == '0' ? '{"value":"pulse"}' : null;
    }
}

//Входящие сообщение от Алисы 0|1 в сообщение dxhome {"state":"OPEN"}|{"state":"CLOSE"}
class _AliseFormatOn_7 implements iAliceFormatter
{
    function convert($value)
    {
        return $value == '1' ? '{"state":"OPEN"}' : '{"state":"CLOSE"}';
    }
}

//Входящие сообщение от Алисы 0..100 в сообщение dxhome 0..9 (0->8, 8->9)
class _AliseFormatBrightness_1 implements iAliceFormatter
{
    function convert($value)
    {
        if (!is_numeric($value)) $data['value'] = '9';
        else {
            $numValue = (int)(round((int)($value) * 8 / 100));
            if ($numValue == 0) $data['value'] = '8';
            elseif ($numValue == 8) $data['value'] = '9';
            else $data['value'] = strval($numValue);
        }
        $data['status'] = statusKey::ALICE;
        return json_encode($data);
    }
}

//Входящие сообщение от Алисы "one"|"two"|"three"|"four" в сообщение dxhome {"demoRun":1|2|3|4}
class _AliseFormatProgram_1 implements iAliceFormatter
{
    function convert($value)
    {
        switch ($value) {
            case '"one"' : return '{"demoRun":1}';
            case '"two"' : return '{"demoRun":2}';
            case '"three"' : return '{"demoRun":3}';
            case '"four"' : return '{"demoRun":4}';
            default : return '';
        }
    }
}

//Входящие сообщение от Алисы 0..n в сообщение dxhome "ledMode":0..n
class _AliseFormatChannel_1 implements iAliceFormatter
{
    function convert($value)
    {
        if (!is_numeric($value)) return '';
        return '{"ledMode":'.(int)$value.'}';
    }
}

//Входящие сообщение от Алисы 0..n в сообщение dxhome {"position":0..n}
class _AliseFormatOpen_1 implements iAliceFormatter
{
    function convert($value)
    {
        if (!is_numeric($value)) return '';
        return '{"position":'.(int)$value.'}';
    }
}


//__STATUS__

//от устройства "state":"ON|OFF" в Алису 0|1
class _AliseFormatOnStat_1 implements iAliceFormatter
{
    function convert($value)
    {
        $data = json_decode($value, true);
        if (array_key_exists('state', $data)) {
            switch (strtolower($data['state'])) {
                case 'on' : return '1';
                case 'off' : return '0';
            }
        }
        return null;
    }
}

//от устройства on|off в Алису 0|1
class _AliseFormatOnStat_2 implements iAliceFormatter
{
    function convert($value)
    {
        switch (strtolower($value)) {
            case 'on' : return '1';
            case 'off' : return '0';
        }
        return null;
    }
}

//Входящие сообщение от Алисы 0..100 в сообщение dxhome 0..9 (0->8, 8->9)
class _AliseFormatBrightnessStat_1 implements iAliceFormatter
{
    function convert($value)
    {
        return null;
    }
}

//от устройства on|off в Алису 0|1
class _AliseFormatProgramStat_1 implements iAliceFormatter
{
    function convert($value)
    {
        $data = json_decode($value, true);
        if (array_key_exists('demoRun', $data)) {
            switch ($data['demoRun']) {
                case 1 :
                    return '"one"';
                case 2 :
                    return '"two"';
                case 3 :
                    return '"three"';
                case 4 :
                    return '"four"';
            }
        }
        return null;
    }
}

//от устройства {"ledMode":0..n} в Алису 0..n
class _AliseFormatChannelStat_1 implements iAliceFormatter
{
    function convert($value)
    {
        $data = json_decode($value, true);
        if (array_key_exists('ledMode', $data)) {
            if (is_int($data['ledMode'])) {
                return strval($data['ledMode']);
            }
        }
        return null;
    }
}

//от устройства {"position":0..n} в Алису 0..n
class _AliseFormatOpenStat_1 implements iAliceFormatter
{
    function convert($value)
    {
        $data = json_decode($value, true);
        if (array_key_exists('position', $data)) {
            if (is_int($data['position'])) {
                return strval($data['position']);
            }
        }
        return null;
    }
}
