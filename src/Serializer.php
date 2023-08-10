<?php

namespace Sentiweb\Rserve;

use Sentiweb\Rserve\REXP;
use Sentiweb\Rserve\REXP\RNull;
use Sentiweb\Rserve\REXP\Symbol;

/**
 * Serialize REXP object to binary protocol representation 
 * sendable to Rserve
 * 
 * @author Clément Turbelin
 */
class Serializer extends Protocol {
	/**
	 *
	 * @param $value
	 * This function is not functional. Please use it only for testing
	 */
	public function serialize(REXP $value) {
		// Current offset
		$o = 0; // Init with header size
		$contents = '';
		$type = $value->getType();
		switch($type) {
			case self::XT_S4:
			case self::XT_NULL:
				break;
			case self::XT_INT:
				$v = (int)$value->at(0);
				$contents .= _rserve_mkint32($v);
				$o += 4;
				break;
			case self::XT_DOUBLE:
				$v = (float)$value->at(0);
				$contents .= _rserve_mkfloat64($v);
				$o += 8;
				break;
			case self::XT_ARRAY_INT:
				$vv = $value->getValues();
				$n = count($vv);
				for($i = 0; $i < $n; ++$i) {
					$v = $vv[$i];
					$contents .= _rserve_mkint32($v);
					$o += 4;
				}
				break;
			case self::XT_ARRAY_BOOL:
				$vv = $value->getValues();
				$n = count($vv);
				$contents .= _rserve_mkint32($n);
				$o += 4;
				if( $n ) {
					for($i = 0; $i < $n; ++$i) {
						$v = $vv[$i];
						if(is_null($v)) {
							$v = 2;
						} else {
							$v = (int)$v;
						}
						if($v != 0 AND $v != 1) {
							$v = 2;
						}
						$contents .= chr($v);
						++$o;
					}
					while( ($o & 3) != 0 ) {
						$contents .= chr(3);
						++$o;
					}
				}
				break;
			case self::XT_ARRAY_DOUBLE:
				$vv = $value->getValues();
				$n = count($vv);
				for($i = 0; $i < $n; ++$i) {
					$v = (float)$vv[$i];
					$contents .= _rserve_mkfloat64($v);
					$o += 8;
				}
				break;
			case self::XT_RAW :
				$v = $value->getValue();
				$n = $value->length();
				$contents .= _rserve_mkint32($n);
				$o += 4;
				$contents .= $v;
				break;
	
			case self::XT_ARRAY_STR:
				$vv = $value->getValues();
				$n = count($vv);
				for($i = 0; $i < $n; ++$i) {
					$v = $vv[$i];
					if( !is_null($v) ) {
						$contents .= $v;
						$contents .= chr(0);
						$o += strlen($v) + 1;
					} else {
						$contents .= chr(255).chr(0);
						$o += 2;
					}
				}
				while( ($o & 3) != 0) {
					$contents .= chr(1);
					++$o;
				}
				break;
			case self::XT_LIST_TAG:
			case self::XT_LIST_NOTAG:
			case self::XT_LANG_TAG:
			case self::XT_LANG_NOTAG:
			case self::XT_LIST:
			case self::XT_VECTOR:
			case self::XT_VECTOR_EXP:
				$l = $value->getValues();
				if($type == XT_LIST_TAG || $type == XT_LANG_TAG) {
					$names = $value->getNames();
				}
				$i = 0;
				$n = count($l);
				while($i < $n) {
					$x = $l[$i];
					if( is_null($x) ) {
						$x = new RNull();
					}
					$iof = strlen($contents);
					$contents .= self::createBinary($x);
					if($type == XT_LIST_TAG || $type == XT_LANG_TAG) {
						$sym = new Symbol();
						$sym->setValue($names[$i]);
						$contents .= $this->serialize($sym);
					}
					++$i;
				}
				break;
	
			case self::XT_SYMNAME:
			case self::XT_STR:
				$s = (string)$value->getValues();
				$contents .= $s;
				$o += strlen($s);
				$contents .= chr(0);
				++$o;
				//padding if necessary
				while( ($o & 3) != 0) {
					$contents .= chr(0);
					++$o;
				}
				break;
		}
		/*
		TODO: handling attr
		$attr = $value->attributes();
		$attr_bin = '';
		if( is_null($attr) ) {
		$attr_off = $this->serialize($attr, $attr_bin, 0);
		$attr_flag = self::XT_HAS_ATTR;
		} else {
		$attr_off = 0;
		$attr_flag = 0;
		}
		// [0]   (4) header SEXP: len=4+m+n, XT_HAS_ATTR is set
		// [4]   (4) header attribute SEXP: len=n
		// [8]   (n) data attribute SEXP
		// [8+n] (m) data SEXP
		*/
		$attr_flag = 0;
		$length = $o;
		$isLarge = ($length > 0xfffff0);
		$code = $type | $attr_flag;
	
		// SEXP Header (without ATTR)
		// [0]  (byte) eXpression Type
		// [1]  (24-bit int) length
		$r  = chr( $code & 255);
		$r .= _rserve_mkint24($length);
		$r .= $contents;
		return $r;
	}
	
	
}