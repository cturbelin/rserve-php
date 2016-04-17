<?php

namespace Sentiweb\Rserve\Parser;

use Sentiweb\Rserve\Parser;
use Sentiweb\Rserve\REXP\Complex;
use Sentiweb\Rserve\REXP\Double;
use Sentiweb\Rserve\REXP\Error;
use Sentiweb\Rserve\REXP\Factor;
use Sentiweb\Rserve\REXP\GenericVector;
use Sentiweb\Rserve\REXP\RList;
use Sentiweb\Rserve\REXP\Integer;
use Sentiweb\Rserve\REXP\Language;
use Sentiweb\Rserve\REXP\Logical;
use Sentiweb\Rserve\REXP\RNull;
use Sentiweb\Rserve\REXP\Raw;
use Sentiweb\Rserve\REXP\String;
use Sentiweb\Rserve\REXP\Symbol;
use Sentiweb\Rserve\REXP\Unknown;
use Sentiweb\Rserve\REXP\Dataframe;

class REXP extends Parser {
	
	/**
	 * SEXP to REXP objects parser
	 */
	public function parse($buf, &$offset) {
		$attr = null;
		$r = $buf;
		$i = $offset;
		
		// some simple parsing - just skip attributes and assume short responses
		$ra = _rserve_int8( $r, $i );
		$rl = _rserve_int24( $r, $i + 1 );
		$i += 4;
		
		$offset = $eoa = $i + $rl;
		if (($ra & 64) == 64) {
			throw new Exception ( 'Long packets are not supported (yet).' );
		}
		
		if ($ra > self::XT_HAS_ATTR) {
			$ra &= ~ self::XT_HAS_ATTR;
			$al = _rserve_int24( $r, $i + 1 );
			$tmp = $i;
			$attr = $this->parse( $buf, $tmp );
			$i += $al + 4;
		}
		
		$class = ($attr) ? $attr->at( 'class' ) : null;
		
		if ($class) {
			$class = $class->getValues();
		}
		switch ($ra) {
			case self::XT_NULL :
				$a = new RNull();
				break;
			
			case self::XT_VECTOR : // generic vector
				$v = array ();
				while ( $i < $eoa ) {
					$v [] = $this->parse( $buf, $i );
				}
				$use_df = false;
				if ($class) {
					if (in_array('data.frame', $class )) {
						$use_df = true;
					}
				}
				$a = $use_df ? new Dataframe() : new GenericVector();
				$a->setValues ( $v );
				break;
			
			case self::XT_SYMNAME : // symbol
				$oi = $i;
				while ( $i < $eoa && ord ( $r [$i] ) != 0 ) {
					$i ++;
				}
				$v = substr ( $buf, $oi, $i - $oi );
				$a = new Symbol ();
				$a->setValue ( $v );
				break;
			
			case self::XT_LIST_NOTAG :
			case self::XT_LANG_NOTAG : // pairlist w/o tags
				$v = array();
				while ( $i < $eoa ) {
					$v [] = $this->parse( $buf, $i );
				}
				$a = ($ra == self::XT_LIST_NOTAG) ? new RList() : new Language();
				$a->setValues( $a );
				break;
			
			case self::XT_LIST_TAG :
			case self::XT_LANG_TAG : // pairlist with tags
				$v = array();
				$names = array();
				while ( $i < $eoa ) {
					$v[] = $this->parse( $buf, $i );
					$names[] = $this->parse( $buf, $i );
				}
				$a = ($ra == self::XT_LIST_TAG) ? new RList() : new Language();
				$a->setValues( $v );
				$a->setNames( $names );
				break;
			
			case self::XT_ARRAY_INT : // integer array
				$v = array ();
				while ( $i < $eoa ) {
					$v [] = _rserve_int32( $r, $i );
					$i += 4;
				}
				$use_factor = false;
				if ($class) {
					if (in_array( 'factor', $class )) {
						$use_factor = true;
					}
				}
				$a = $use_factor ? new Factor() : new Integer();
				$a->setValues( $v );
				break;
			
			case self::XT_ARRAY_DOUBLE : // double array
				$v = array ();
				while ( $i < $eoa ) {
					$v [] = _rserve_flt64( $r, $i );
					$i += 8;
				}
				$a = new Double();
				$a->setValues( $v );
				break;
			
			case self::XT_ARRAY_STR : // string array
				$v = array();
				$oi = $i;
				while ( $i < $eoa ) {
					if ( ord( $r[$i] ) == 0) {
						$v[] = substr( $r, $oi, $i - $oi );
						$oi = $i + 1;
					}
					$i++;
				}
				
				if($class && in_array('try-error', $class)) {
					$a = new Error();
				} else {
					$a = new String();
				}
				$a->setValues( $v );
				break;
			
			case self::XT_ARRAY_BOOL : // boolean vector
				$n = _rserve_int32( $r, $i );
				$i += 4;
				$k = 0;
				$vv = array();
				while ( $k < $n ) {
					$v = _rserve_int8( $r, $i ++ );
					$vv[$k] = ($v == 1) ? true : (($v == 0) ? false : null);
					$k ++;
				}
				$a = new Logical();
				$a->setValues( $vv );
				break;
			
			case self::XT_RAW : // raw vector
				$len = _rserve_int32( $r, $i );
				$i += 4;
				$v = substr( $r, $i, $len );
				$a = new Raw();
				$a->setValue( $v );
				break;
			
			case self::XT_ARRAY_CPLX :
				$v = array();
				while ( $i < $eoa ) {
					$real = _rserve_flt64( $r, $i );
					$i += 8;
					$im = _rserve_flt64( $r, $i );
					$i += 8;
					$v [] = array ($real, $im);
				}
				$a = new Complex();
				$a->setValues( $v );
				break;
			/*
			 * case 48: // unimplemented type in Rserve
			 * $uit = _rserve_int32($r, $i);
			 * // echo "Note: result contains type #$uit unsupported by Rserve.<br/>";
			 * $a = null;
			 * break;
			 */
			default :
				// handle unknown type
				$a = new Unknown( $ra );
		}
		if ($attr && is_object( $a )) {
			$a->setAttributes( $attr );
		}
		return $a;
	}
}