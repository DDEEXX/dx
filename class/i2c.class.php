<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.04.19
 * Time: 21:14
 */

class i2c
{
    /**
     * Считывает с сети i2c данные
     * @param $bus
     * @param $address
     * @param $register
     * @param string $mode
     * @return string
     */
    private static function readRegister($bus, $address, $register, $mode = 'b')
    {
        $cmd = 'i2cget -y ' . $bus . ' ' . $address . ' ' . $register . ' ' . $mode;
        return trim(shell_exec($cmd));
    }

    /**
     * Записывает в i2c сеть данные
     * @param $bus
     * @param $address
     * @param $register
     * @param $value
     * @param string $mode
     */
    private static function writeRegister($bus, $address, $register, $value, $mode = 'b')
    {
        $cmd = 'i2cset -y ' . $bus . ' ' . $address . ' ' . $register . ' ' . $value . ' ' . $mode;
        shell_exec($cmd);
    }

    /**
     * Записывает в сеть байт value
     * @param $bus
     * @param $address
     * @param $register
     * @param $value
     */
    public static function writeByte($bus, $address, $register, $value)
    {
        self::writeRegister($bus, $address, $register, $value, 'b');
    }

    /**
     * Считывает с сети беззнаквый 1 байт по адресу
     * @param $bus
     * @param $address
     * @param $register
     * @return int
     */
    public static function readByte($bus, $address, $register)
    {
        $dec_val = intval(self::readRegister($bus, $address, $register), 16);
        return $dec_val;
    }

    /**
     * Считывает с сети 2 байта по адресу и приводит из к зноковому или беззнаковому short
     * @param $bus
     * @param $address
     * @param $register
     * @param $format
     * @return int
     */
    private static function readWord($bus, $address, $register, $format)
    {
        $val = intval(self::readRegister($bus, $address, $register, 'w'), 16);
        $arr = unpack($format, pack('n', $val));
        $dec_val = $arr[1];
        return $dec_val;
    }

    /**
     * Возвращает знаковое целое число (2 байта)
     * @param $bus
     * @param $address
     * @param $register
     * @return int
     */
    public static function readShort($bus, $address, $register)
    {
        return self::readWord($bus, $address, $register, 's');
    }

    /**
     * Возвращает беззнаковое целое число (2 байта)
     * @param $bus
     * @param $address
     * @param $register
     * @return int
     */
    public static function readUnShort($bus, $address, $register)
    {
        return self::readWord($bus, $address, $register, 'S');
    }


}