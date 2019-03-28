<?php
/* ------------------------------------------------------------------------------------------------
Copyright © 2016, Viacheslav Baczynski, @V_Baczynski
License: MIT License
PHP IIC Library, v1.0
Simple functions to read from and write to registers on device via I2C bus using i2c-tools.
TODO:
	Define the following variables in your file:
	$block			// Block name for the I2C device on the system
	$i2c_address	// I2C slave address (address of the device)
------------------------------------------------------------------------------------------------ */

# READ FUNCTIONS ----------------------------------------------------------------------------------
function read_register(
    $register	// register in the i2c device
){
    $cmd = 'i2cget -y ' . $GLOBALS['block'] . ' ' . $GLOBALS['i2c_address'] . ' ' . $register . ' b';
    return trim( shell_exec( $cmd ) );
}

function read_byte(
    $reg	// register with most significant byte
){
    $dec_val = intval( read_register( $reg ), 16 );
    return $dec_val;
}

function read_short(
    $reg_msb	// register with most significant byte
){
    $msb = intval( read_register( $reg_msb++ ), 16 );
    $lsb = intval( read_register( $reg_msb ), 16 );
    $val = ( $msb << 8 ) | $lsb;
    $arr = unpack( 's', pack( 'n', $val ) );
    $dec_val = $arr[1];
    //echo "DEBUG(read_short): " . $dec_val . "\n";
    return $dec_val;
}
function read_ushort(
    $reg_msb	// register with most significant byte
){
    $msb = intval( read_register( $reg_msb++ ), 16 );
    $lsb = intval( read_register( $reg_msb ), 16 );
    $val = ( $msb << 8 ) | $lsb;
    $arr = unpack( 'S', pack( 'n', $val ) );
    $dec_val = $arr[1];
    //echo "DEBUG(read_ushort): " . $dec_val . "\n";
    return $dec_val;
}
function read_ulong(
    $reg_msb	// register with most significant byte
){
    $msb= intval( read_register( $reg_msb++ ), 16 );
    $lsb= intval( read_register( $reg_msb++ ), 16 );
    $xlsb = intval( read_register( $reg_msb ), 16 );
    $val = ( $msb << 16 ) | ( $lsb << 8 ) | $xlsb;
    $arr = unpack( 'l', pack( 'N', $val ) );
    $dec_val = $arr[1];
    //echo "DEBUG(read_ulong): " . $dec_val . "\n";
    return $dec_val;
}
# WRITE FUNCTIONS ---------------------------------------------------------------------------------
function write_register(
    $register,	// register address
    $value		// data to be written
){
    shell_exec( 'i2cset -y ' . $GLOBALS['block'] . ' ' . $GLOBALS['i2c_address'] . ' ' . $register . ' ' . $value . ' b' );
}
function write_short(
    $register,	// register address
    $value		// data to be written
){
    $value = $value & 0xFF;
    write_register( $register, $value );
}