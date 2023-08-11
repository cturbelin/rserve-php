<?php

namespace Sentiweb\Rserve\Parser;

use Sentiweb\Rserve\Parser;
use Sentiweb\Rserve\ArrayWrapper;

/**
 * Parse SEXP to debug array
 * 
 * array(type, length, offset, contents, n)
 * 
 * @author Clément Turbelin
 *
 */
class Debug extends Parser {
	/**
	 * parse SEXP to Debug array(type, length,offset, contents, n)
	 * 
	 * @param string $buf        	
	 * @param int $offset        	
	 */
	public function parse($buf, &$offset) {
		$r = $buf;
		$i = $offset;
		
		// some simple parsing - just skip attributes and assume short responses
		$attr = null;
		$ra = _rserve_int8 ( $r, $i );
		$rl = _rserve_int24 ( $r, $i + 1 );
		$i += 4;
		
		$offset = $eoa = $i + $rl;
		
		$result = [];
		
		$result ['type'] = self::xtName ( $ra & 63 );
		$result ['length'] = $rl;
		$result ['offset'] = $i;
		$result ['eoa'] = $eoa;
		if (($ra & 64) == 64) {
			$result ['long'] = true;
			return $result;
		}
		if ($ra > self::XT_HAS_ATTR) {
			$ra &= ~ self::XT_HAS_ATTR;
			$al = _rserve_int24 ( $r, $i + 1 );
			$tmp = $i; // use temporary to protect current offset
			$attr = $this->parse ( $buf, $tmp );
			$result ['attr'] = $attr;
			$i += $al + 4; // add attribute length
		}
		if ($ra == self::XT_NULL) {
			return $result;
		}
		if ($ra == self::XT_VECTOR) { // generic vector
			$a = [];
			while ( $i < $eoa ) {
				$a [] = $this->parse ( $buf, $i );
			}
			$result ['contents'] = $a;
		}
		if ($ra == self::XT_SYMNAME) { // symbol
			$oi = $i;
			while ( $i < $eoa && ord ( $r [$i] ) != 0 ) {
				$i ++;
			}
			$result ['contents'] = substr ( $buf, $oi, $i - $oi );
		}
		if ($ra == self::XT_LIST_NOTAG || $ra == self::XT_LANG_NOTAG) { // pairlist w/o tags
			$a = [];
			while ( $i < $eoa )
				$a [] = $this->parse ( $buf, $i );
			$result ['contents'] = $a;
		}
		if ($ra == self::XT_LIST_TAG || $ra == self::XT_LANG_TAG) { // pairlist with tags
			$a = [];
			while ( $i < $eoa ) {
				$val = $this->parse ( $buf, $i );
				$tag = $this->parse( $buf, $i );
				$a [$tag] = $val;
			}
			$result ['contents'] = $a;
		}
		if ($ra == self::XT_ARRAY_INT) { // integer array
			$a = [];
			while ( $i < $eoa ) {
				$a [] = _rserve_int32 ( $r, $i );
				$i += 4;
			}
			if (count ( $a ) == 1) {
				$result ['contents'] = $a [0];
			}
			$result ['contents'] = $a;
		}
		if ($ra == self::XT_ARRAY_DOUBLE) { // double array
			$a = [];
			while ( $i < $eoa ) {
				$a [] = _rserve_flt64 ( $r, $i );
				$i += 8;
			}
			if (count ( $a ) == 1) {
				$result ['contents'] = $a [0];
			}
			$result ['contents'] = $a;
		}
		if ($ra == self::XT_ARRAY_STR) { // string array
			$a = [];
			$oi = $i;
			while ( $i < $eoa ) {
				if (ord ( $r [$i] ) == 0) {
					$a [] = substr ( $r, $oi, $i - $oi );
					$oi = $i + 1;
				}
				$i ++;
			}
			if (count ( $a ) == 1) {
				$result ['contents'] = $a [0];
			}
			$result ['contents'] = $a;
		}
		if ($ra == self::XT_ARRAY_BOOL) { // boolean vector
			$n = _rserve_int32 ( $r, $i );
			$result ['size'] = $n;
			$i += 4;
			$k = 0;
			$a = [];
			while ( $k < $n ) {
				$v = _rserve_int8 ( $r, $i ++ );
				$a [$k] = ($v === 1) ? true : (($v === 0) ? false : null);
				++ $k;
			}
			if (count ( $a ) == 1) {
				$result ['contents'] = $a [0];
			}
			$result ['contents'] = $a;
		}
		if ($ra == self::XT_RAW) { // raw vector
			$len = _rserve_int32 ( $r, $i );
			$i += 4;
			$result ['size'] = $len;
			$result ['contents'] = substr ( $r, $i, $len );
		}
		if ($ra == self::XT_ARRAY_CPLX) {
			$real = [];
			$im = [];
			while ( $i < $eoa ) {
				$real [] = _rserve_flt64 ( $r, $i );
				$i += 8;
				$im [] = _rserve_flt64 ( $r, $i );
				$i += 8;
			}
			if (count ( $real ) == 1) {
				$a = [$real [0], $im [0]];
			} else {
				$a = [$real, $im];
			}
			$result ['contents'] = $a;
		}
		if ($ra == 48) { // unimplemented type in Rserve
			$uit = _rserve_int32 ( $r, $i );
			$result ['unknownType'] = $uit;
		}
		return $result;
	}
}
