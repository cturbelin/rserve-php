<?php
/**
* Rserve client for PHP
* Supports Rserve protocol 0103 only (used by Rserve 0.5 and higher)
* $Revision$
* @author Clément TURBELIN
* Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
*/

/**
 * Read byte from a binary packed format @see Rserve protocol
 * @param string $buf buffer
 * @param int $o offset
 */
function int8($buf, $o = 0) {
	return ord($buf[$o]);
}

/**
 * Read an integer from a 24 bits binary packed format @see Rserve protocol
 * @param string $buf buffer
 * @param int $o offset
 */
function int24($buf, $o = 0) {
	return (ord($buf[$o]) | (ord($buf[$o + 1]) << 8) | (ord($buf[$o + 2]) << 16));
}

/**
 * Read an integer from a 32 bits binary packed format @see Rserve protocol
 * @param string $buf buffer
 * @param int $o offset
 */
function int32($buf, $o=0) {
	return (ord($buf[$o]) | (ord($buf[$o + 1]) << 8) | (ord($buf[$o + 2]) << 16) | (ord($buf[$o + 3]) << 24));
}

/**
 * One Byte
 * @param $i
 */
function mkint8($i) {
	return chr($i & 255);
}

/**
 * Make a binary representation of integer using 32 bits
 * @param int $i
 * @return string
 */
function mkint32($i) {
	$r = chr($i & 255); 
	$i >>= 8; 
	$r .= chr($i & 255); 
	$i >>=8; 
	$r .= chr($i & 255); 
	$i >>=8; 
	$r .= chr($i & 255);
	return $r;
}

/*
 * Create a 24 bit integer
 * @return string binary representation of the int using 24 bits 
 */
function mkint24($i) {
	$r = chr($i & 255); 
	$i >>= 8; 
	$r .= chr($i & 255); 
	$i >>=8; 
	$r .= chr($i & 255);
	return $r;
}

/**
 * Create a binary representation of float to 64bits
 * TODO: works only for intel endianess, should be adapted for no big endian proc
 * @param double $v 
 */
function mkfloat64($v) {
	return pack('d', $v);
}

/**
 * 64bit integer to Float
 * @param $buf
 * @param $o
 */
function flt64($buf, $o = 0) {
	$ss = substr($buf, $o, 8);
	if (Rserve_Connection::$machine_is_bigendian) {
		for ($k = 0; $k < 7; $k++) { 
			$ss[7 - $k] = $buf[$o + $k];
		}	
	} 
	$r = unpack('d', substr($buf, $o, 8)); 
	return $r[1]; 
}

/**
 * Create a packet for QAP1 message
 * @param int $cmd command identifier
 * @param string $string contents of the message
 */
function _rserve_make_packet($cmd, $string) {
	$n = strlen($string) + 1; 
	$string .= chr(0);
	while (($n & 3) != 0) { 
		$string .= chr(1); 
		$n++; 
	}
	// [0]  (int) command
  	// [4]  (int) length of the message (bits 0-31)
  	// [8]  (int) offset of the data part
  	// [12] (int) length of the message (bits 32-63)
	return mkint32($cmd) . mkint32($n + 4) . mkint32(0) . mkint32(0) . chr(4) . mkint24($n) . $string;
}

/**
 * Make a data packet
 * @param unknown_type $type
 * @param unknown_type $string NULL terminated string
 */
function _rserve_make_data($type, $string) {
	$s = '';
	$len = strlen($string); // Length of the binary string
	$is_large = $len > 0xfffff0;
	$pad = 0; // Number of padding needed
	while( ($len & 3) != 0) { 
		// ensure the data packet size is divisible by 4
		++$len;
		++$pad;
	} 
	$s .= chr($type & 255) | ($is_large ? Rserve_Connection::DT_LARGE : 0);
	$s .= chr($len & 255);
	$s .= chr( ($len & 0xff00) >> 8);
	$s .= chr( ($len & 0xff0000) >> 16); 	
	if($is_large) {
		$s .= chr(($len & 0xff000000) >> 24).chr(0).chr(0).chr(0);
	}
	$s .= $string;
	if($pad) {
		$s .= str_repeat(chr(0), $pad);
	}
}
