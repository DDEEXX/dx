<?php
/* ------------------------------------------------------------------------------------------------
Copyright © 2016, Viacheslav Baczynski, @V_Baczynski
License: MIT License
BMP sensor reader, v1.0.
Reading data from and writing to BMP085 sensor via I2C.
Info: The BMP085 consists of a piezo-resistive sensor, an analog to digital converter
and a control unit with E2PROM and a serial I2C interface. The BMP085 delivers the
uncompensated value of pressure and temperature. The E2PROM has stored 176 bit of
individual calibration data. This is used to compensate offset, temperature dependence
and other parameters of the sensor.
• UP = pressure data (16 to 19 bit)
• UT = temperature data (16 bit)
------------------------------------------------------------------------------------------------ */
// IIC library
//include 'phpi2c.php';
//include(dirname(__FILE__) . '/class/phpi2c.php');
include(dirname(__FILE__) . '/class/i2c.class.php');
require_once(dirname(__FILE__) . '/class/managerUnits.class.php');

$tekUnit = managerUnits::getUnitLabel('temp_cubie');
$val = $tekUnit->getValue();


$bus = "1";    // block name on the system
$i2c_address = "0x77";    // i2c slave address for bmp085
//
// i2c device operation modes
# low power			-> 0
# standard			-> 1
# high res			-> 2
# ultra high res	-> 3
$oss = 1; // oversampling setting
$sleep_time = array(
    0 => 4600, // 4.5 ms according to documentation, but let's put a little bit more
    1 => 7600, // 7.5 ms
    2 => 13600, // 13.5 ms
    3 => 25600 // 25.5 ms
);
// chip calibration coefficients (static for sensor and can be saved in a file)
$ac1 = i2c::readShort($bus, $i2c_address, 0xAA);
$ac2 = i2c::readShort($bus, $i2c_address, 0xAC);
$ac3 = i2c::readShort($bus, $i2c_address, 0xAE);
$ac4 = i2c::readUnShort($bus, $i2c_address, 0xB0);
$ac5 = i2c::readUnShort($bus, $i2c_address, 0xB2);
$ac6 = i2c::readUnShort($bus, $i2c_address, 0xB4);
$b1 = i2c::readShort($bus, $i2c_address, 0xB6);
$b2 = i2c::readShort($bus, $i2c_address, 0xB8);
$mb = i2c::readShort($bus, $i2c_address, 0xBA);
$mc = i2c::readShort($bus, $i2c_address, 0xBC);
$md = i2c::readShort($bus, $i2c_address, 0xBE);
// sensor readings
$ut = 0; // uncompensated temperature
$t = 0; // true temperature
$up = 0; // uncompensated pressure
$p = 0; // true pressure

// reading uncompensated temperature
i2c::writeByte($bus, $i2c_address, 0xF4, 0x2E);
usleep(4600); // Should be not less then 4500
$msb = i2c::readByte($bus, $i2c_address, 0xF6);
$lsb = i2c::readByte($bus, $i2c_address, 0xF7);
$ut = $msb << 8 | $lsb;

// reading uncompensated pressure
i2c::writeByte($bus, $i2c_address, 0xF4, 0x34 + ($oss << 6));
usleep($sleep_time[$oss]);
$msb_p = i2c::readByte($bus, $i2c_address, 0xF6);
$lsb_p = i2c::readByte($bus, $i2c_address, 0xF7);
$xlsb_p = i2c::readByte($bus, $i2c_address, 0xF8);
$up = ($msb_p << 16 | $lsb_p << 8 | $xlsb_p) >> (8 - $oss);

// calculating true temperature
$x1 = (($ut - $ac6) * $ac5) / 32768;
$x2 = ($mc * 2048) / ($x1 + $md);
$b5 = $x1 + $x2;
$t = ($b5 + 8) / 160;

//echo '$ac1: ' . $ac1 ."<br>";
//echo '$ac2: ' . $ac2 ."<br>";
//echo '$ac3: ' . $ac3 ."<br>";
//echo '$ac4: ' . $ac4 ."<br>";
//echo '$ac5: ' . $ac5 ."<br>";
//echo '$ac6: ' . $ac6 ."<br>";
//echo '$b1: ' . $b1 ."<br>";
//echo '$b2: ' . $b2 ."<br>";
//echo '$mb: ' . $mb ."<br>";
//echo '$mc: ' . $mc ."<br>";
//echo '$md: ' . $md ."<br>";
//echo '$msb: ' . $msb ."<br>";
//echo '$lsb: ' . $lsb ."<br>";
//echo '$ut: ' . $ut ."<br>";
//echo '$msb_p: ' . $msb_p ."<br>";
//echo '$lsb_p: ' . $lsb_p ."<br>";
//echo '$xlsb_p: ' . $xlsb_p ."<br>";
//echo '$up: ' . $up ."<br>";

// calculating true pressure
$b6 = $b5 - 4000;
$x1 = ($b2 * (($b6 ^ 2) >> 12)) >> 11;
$x2 = ($ac2 * $b6) >> 11;
$x3 = $x1 + $x2;
$b3 = ((($ac1 * 4 + $x3) << $oss) + 2) / 4;
$x1 = ($ac3 * $b6) >> 13;
$x2 = ($b1 * ($b6 ^ 2) >> 12) >> 16;
$x3 = (($x1 + $x2) + 2) >> 2;
$b4 = ($ac4 * ($x3 + 32768)) >> 15;
$b7 = ($up - $b3) * (50000 >> $oss);
if ($b7 < 0x80000000) {
    $p = ($b7 * 2) / $b4;
}
else {
    $p = ($b7 / $b4) * 2;
}
$x1 = ($p >> 8) * ($p >> 8);
$x1 = ($x1 * 3038) >> 16;
$x2 = (-7357 * $p) >> 16;
$p = $p + (($x1 + $x2 + 3791) >> 4);
$p = $p*0.0075;

//// calculating absolute altitude
//$a = 44330 * ( 1 - pow( ( $p / 101625 ), 0.1903 ) );
//// calculating pressure at sea level
//$p0 = $p / pow( (1 - $a / 44330), 5.255 );
//// Making it prettier
//$a = intval( $a );
//// converting Pressure from hPa to mm Hg
//$p = intval( $p / 1.3332239);
//$p = $p / 100;
//$p0 = intval( $p0 / 1.3332239);
//$p0 = $p0 / 100;
//echo "Temperature: " . $t . " C.\nPressure: " . $p . " mm Hg.\nAbsolute altitude: " . $a . "m.\nCalculated pressure at sea level: " . $p0 ."\n";

$i2c_address = "0x48";    // i2c slave address for bmp085
$ut = $ac1 = i2c::readUnShort($bus, $i2c_address, 0x00);
$ut = $ut >> 5;
$tt = $ut * 0.125;

echo "Temperature: " . $t . "<br>";
echo "Temperature1: " . $tt . "<br>";
echo "Pressure: " . $p . "<br>";

