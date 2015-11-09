<?php
/**
 * Rserve message Parser
 * @author Clément Turbelin
 * From Rserve java Client & php Client 
 * Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
 */
class Rserve_Parser {

	/** xpression type: NULL */
	const XT_NULL =  0;

	/** xpression type: integer */
	const XT_INT = 1;

	/** xpression type: double */
	const XT_DOUBLE = 2;

	/** xpression type: String */
	const XT_STR = 3;

	/** xpression type: language construct (currently content is same as list) */
	const XT_LANG = 4;

	/** xpression type: symbol (content is symbol name: String) */
	const XT_SYM = 5;

	/** xpression type: RBool */
	const XT_BOOL = 6;

	/** xpression type: S4 object
	@since Rserve 0.5 */
	const XT_S4 = 7;

	/** xpression type: generic vector (RList) */
	const XT_VECTOR = 16;

	/** xpression type: dotted-pair list (RList) */
	const XT_LIST = 17;

	/** xpression type: closure (there is no java class for that type (yet?). currently the body of the closure is stored in the content part of the REXP. Please note that this may change in the future!) */
	const XT_CLOS = 18;

	/** +xpression type: symbol name @since Rserve 0.5 */
	const XT_SYMNAME = 19;

	/** xpression type: dotted-pair list (w/o tags)	@since Rserve 0.5 */

	const XT_LIST_NOTAG = 20;

	/** xpression type: dotted-pair list (w tags) @since Rserve 0.5 */
	const XT_LIST_TAG = 21;

	/** xpression type: language list (w/o tags)
	@since Rserve 0.5 */
	const XT_LANG_NOTAG = 22;

	/** xpression type: language list (w tags)
	@since Rserve 0.5 */
	const XT_LANG_TAG = 23;

	/** xpression type: expression vector */
	const XT_VECTOR_EXP = 26;

	/** xpression type: string vector */
	const XT_VECTOR_STR = 27;

	/** xpression type: int[] */
	const XT_ARRAY_INT = 32;

	/** xpression type: double[] */
	const XT_ARRAY_DOUBLE = 33;

	/** xpression type: String[] (currently not used, Vector is used instead) */
	const XT_ARRAY_STR = 34;

	/** internal use only! this constant should never appear in a REXP */
	const XT_ARRAY_BOOL_UA = 35;

	/** xpression type: RBool[] */
	const XT_ARRAY_BOOL = 36;

	/** xpression type: raw (byte[])
	@since Rserve 0.4-? */
	const XT_RAW = 37;

	/** xpression type: Complex[]
	@since Rserve 0.5 */
	const XT_ARRAY_CPLX = 38;

	/** xpression type: unknown; no assumptions can be made about the content */
	const XT_UNKNOWN = 48;

	/** xpression type: RFactor; this XT is internally generated (ergo is does not come from Rsrv.h) to support RFactor class which is built from XT_ARRAY_INT */
	const XT_FACTOR = 127;

	/** used for transport only - has attribute */
	const XT_HAS_ATTR = 128;

    
	/**
	* Global parameters to parse() function
	* If true, use Rserve_RNative wrapper instead of native array to handle attributes
	*/
	public static $use_array_object = FALSE;
    
    /**
    * Transform factor to native strings, only for parse() method
    * If false, factors are parsed as integers
    */
    public static $factor_as_string = TRUE;
    
	/**
	 * SEXP to php array parser 
	 * parse SEXP results -- limited implementation for now (large packets and some data types are not supported)
	 * @param string $buf
	 * @param int $offset
	 * @return native php array or a RNative object if if the static property $use_array_object is TRUE 
	 */
	public static function parse($buf, &$offset) {
		
        $attr = NULL;
        $r = $buf;
		$i = $offset;

		// some simple parsing - just skip attributes and assume short responses
		$ra = int8($r, $i);
		$rl = int24($r, $i + 1);
		$i += 4;

		$offset = $eoa = $i + $rl;
		//echo '[ '.self::xtName($ra & 63).', length '.$rl.' ['.$i.' - '.$eoa.']<br/>';
		if (($ra & 64) == 64) {
			throw new Exception('long packets are not supported (yet).');
		}
		if ($ra > self::XT_HAS_ATTR) {
			//echo '(ATTR*[';
			$ra &= ~self::XT_HAS_ATTR;
			$al = int24($r, $i + 1);
            $tmp = $i; // use temporary to protect current offset
			$attr = self::parse($buf, $tmp);
			//echo '])';
			$i += $al + 4;
		}
        switch($ra) {
            case self::XT_NULL:
                $a = NULL;
                break;
            case self::XT_VECTOR: // generic vector
                $a = array();
                while ($i < $eoa) {
                    $a[] = self::parse($buf, $i);
                }
                // if the 'names' attribute is set, convert the plain array into a map
                if ( isset($attr['names']) ) {
                    $names = $attr['names'];
                    $na = array();
                    $n = count($a);
                    for ($k = 0; $k < $n; $k++) {
                        $na[$names[$k]] = $a[$k];
                    }
                    $a = $na;
                }
            break;
            
            case self::XT_INT:
                $a = int32($r, $i);
                $i += 4;
            break;
            
            case self::XT_DOUBLE:
                $a = flt64($r, $i);
                $i += 8;
            break;
            
            case self::XT_BOOL:
                $v = int8($r, $i++);
                $a = ($v == 1) ? TRUE : (($v == 0) ? FALSE : NULL);
            break;
            
            case self::XT_SYM:
            case self::XT_SYMNAME: // symbol
                $oi = $i;
                while ($i < $eoa && ord($r[$i]) != 0) {
                    $i++;
                }
                $a = substr($buf, $oi, $i - $oi);
            break;
            
            case self::XT_LANG_NOTAG:
            case self::XT_LIST_NOTAG : // pairlist w/o tags
                $a = array();
                while ($i < $eoa) {
                    $a[] = self::parse($buf, $i);
                }
            break;
            
            case self::XT_LIST_TAG:
            case self::XT_LANG_TAG:
                // pairlist with tags
                $a = array();
                while ($i < $eoa) {
                    $val = self::parse($buf, $i);
                    $tag = self::parse($buf, $i);
                    $a[$tag] = $val;
                }
            break;
            
            case self::XT_ARRAY_INT: // integer array
                $a = array();
                while ($i < $eoa) {
                    $a[] = int32($r, $i);
                    $i += 4;
                }
                if (count($a) == 1) {
                    $a = $a[0];
                }
                // If factor, then transform to characters
                if( self::$factor_as_string  and isset($attr['class']) ) {
                    $c = $attr['class'];
                    $is_factor = is_string($c) && ($c == 'factor');
                    if($is_factor) {
                        $n = count($a);
                        $levels = $attr['levels'];
                        for($k = 0; $k < $n; ++$k) {
                            $i = $a[$k];
                            if($i < 0) {
                                $a[$k] = NULL;
                            } else {
                                $a[$k] = $levels[ $i -1];       
                            }
                        }
                    }
                }
            break;
            
            case self::XT_ARRAY_DOUBLE:// double array
                $a = array();
                while ($i < $eoa) {
                    $a[] = flt64($r, $i);
                    $i += 8;
                }
                if (count($a) == 1) {
                    $a = $a[0];
                }
            break;
            
            case self::XT_ARRAY_STR: // string array
                $a = array();
                $oi = $i;
                while ($i < $eoa) {
                    if (ord($r[$i]) == 0) {
                        $a[] = substr($r, $oi, $i - $oi);
                        $oi = $i + 1;
                    }
                    $i++;
                }
                if (count($a) == 1) {
                    $a = $a[0];
                }
            break;
            
            case self::XT_ARRAY_BOOL:  // boolean vector
                $n = int32($r, $i);
                $i += 4;
                $k = 0;
                $a = array();
                while ($k < $n) {
                    $v = int8($r, $i++);
                    $a[$k++] = ($v == 1) ? TRUE : (($v == 0) ? FALSE : NULL);
                }
                if ($n == 1) {
                    $a =  $a[0];
                }
            break;
            
            case self::XT_RAW: // raw vector
                $len = int32($r, $i);
                $i += 4;
                $a =  substr($r, $i, $len);
            break;
            
            case self::XT_ARRAY_CPLX:
                // real part
                $real = array();
                while ($i < $eoa) {
                    $real[] = flt64($r, $i);
                    $i += 8;
                    $im[] = flt64($r, $i);
                    $i += 8;
                }
                if (count($real) == 1) {
                    $a = array($real[0], $im[0]);
                } else {
                    $a = array($real, $im);
                }
            break;
        
            case 48: // unimplemented type in Rserve
                $uit = int32($r, $i);
                // echo "Note: result contains type #$uit unsupported by Rserve.<br/>";
                $a = NULL;
            break;
            
            default:
                echo 'Warning: type '.$ra.' is currently not implemented in the PHP client.';
                $a = NULL;
        } // end switch
        
        if( self::$use_array_object ) {
            if( is_array($a) & $attr) {
                return new Rserve_RNative($a, $attr, $ra);
            } else {
                return $a;
            }
        }
        return $a;
	}

	
	/**
	 * parse SEXP to Debug array(type, length,offset, contents, n)
	 * @param string $buf
	 * @param int $offset
	 */
	public static function parseDebug($buf, &$offset) {
		$r = $buf;
		$i = $offset;

		// some simple parsing - just skip attributes and assume short responses
		$attr =  NULL;
        $ra = int8($r, $i);
		$rl = int24($r, $i + 1);
		$i += 4;

		$offset = $eoa = $i + $rl;
		
		$result = array();
		
		$result['type'] = self::xtName($ra & 63);
		$result['length'] =  $rl;
		$result['offset'] = $i;
		$result['eoa'] = $eoa;
		if (($ra & 64) == 64) {
			$result['long'] = TRUE;
			return $result;
		}
		if ($ra > self::XT_HAS_ATTR) {
			$ra &= ~self::XT_HAS_ATTR;
			$al = int24($r, $i + 1);
            $tmp = $i; // use temporary to protect current offset
			$attr = self::parseDebug($buf, $tmp);
			$result['attr'] = $attr;
			$i += $al + 4; // add attribute length
		}
		if ($ra == self::XT_NULL) {
			return $result;
		}
		if ($ra == self::XT_VECTOR) { // generic vector
			$a = array();
			while ($i < $eoa) {
				$a[] = self::parseDebug($buf, $i);
			}
			$result['contents'] = $a;			
		}
		if ($ra == self::XT_SYMNAME) { // symbol
			$oi = $i;
			while ($i < $eoa && ord($r[$i]) != 0) {
				$i++;
			}
			$result['contents'] = substr($buf, $oi, $i - $oi);
		}
		if ($ra == self::XT_LIST_NOTAG || $ra == self::XT_LANG_NOTAG) { // pairlist w/o tags
			$a = array();
			while ($i < $eoa) $a[] = self::parseDebug($buf, $i);
			$result['contents'] = $a;
		}
		if ($ra == self::XT_LIST_TAG || $ra == self::XT_LANG_TAG) { // pairlist with tags
			$a = array();
			while ($i < $eoa) {
				$val = self::parseDebug($buf, $i);
				$tag = self::parse($buf, $i);
				$a[$tag] = $val;
			}
			$result['contents'] = $a;
		}
		if ($ra == self::XT_ARRAY_INT) { // integer array
			$a = array();
			while ($i < $eoa) {
				$a[] = int32($r, $i);
				$i += 4;
			}
			if (count($a) == 1) {
				$result['contents'] = $a[0];
			}
			$result['contents'] = $a;
		}
		if ($ra == self::XT_ARRAY_DOUBLE) { // double array
			$a = array();
			while ($i < $eoa) {
				$a[] = flt64($r, $i);
				$i += 8;
			}
			if (count($a) == 1) {
				$result['contents'] = $a[0];
			}
			$result['contents'] = $a;
		}
		if ($ra == self::XT_ARRAY_STR) { // string array
			$a = array();
			$oi = $i;
			while ($i < $eoa) {
				if (ord($r[$i]) == 0) {
					$a[] = substr($r, $oi, $i - $oi);
					$oi = $i + 1;
				}
				$i++;
			}
			if (count($a) == 1) {
				$result['contents'] = $a[0];
			}
			$result['contents'] = $a;
		}
		if ($ra == self::XT_ARRAY_BOOL) {  // boolean vector
			$n = int32($r, $i);
			$result['size'] = $n;
			$i += 4;
			$k = 0;
			$a = array();
			while ($k < $n) {
				$v = int8($r, $i++);
				$a[$k] = ($v === 1) ? TRUE : (($v === 0) ? FALSE : NULL);
				++$k;
			}
			if (count($a) == 1) {
				$result['contents'] = $a[0];
			}
			$result['contents'] = $a;
		}
		if ($ra == self::XT_RAW) { // raw vector
			$len = int32($r, $i);
			$i += 4;
			$result['size'] = $len;
			$result['contents'] = substr($r, $i, $len);
		}
		if($ra == self::XT_ARRAY_CPLX) {
			$real = array();
			$im = array();
			while ($i < $eoa) {
				$real[] = flt64($r, $i);
				$i += 8;
				$im[] = flt64($r, $i);
				$i += 8;
			}
            if (count($real) == 1) {
				$a = array($real[0], $im[0]);
			} else {
                $a = array($real, $im);
            }
			$result['contents'] = $a;
		}
		if ($ra == 48) { // unimplemented type in Rserve
			$uit = int32($r, $i);
			$result['unknownType'] = $uit;
		}
		return $result;
	}
	
	/**
	* SEXP to REXP objects parser
	*/
	public static function parseREXP($buf, &$offset) {
		$attr = NULL;
        $r = $buf;
		$i = $offset;

		// some simple parsing - just skip attributes and assume short responses
		$ra = int8($r, $i);
		$rl = int24($r, $i + 1);
		$i += 4;

		$offset = $eoa = $i + $rl;
		if (($ra & 64) == 64) {
			throw new Exception('Long packets are not supported (yet).');
		}

		if ($ra > self::XT_HAS_ATTR) {
			$ra &= ~self::XT_HAS_ATTR;
			$al = int24($r, $i + 1);
            $tmp = $i;
			$attr = self::parseREXP($buf, $tmp);
			$i += $al + 4;
		}
		
		$class = ($attr) ? $attr->at('class') : null;
		if( $class ) {
			$class = $class->getValues();
		}
		
		switch($ra) {
			case self::XT_NULL:
				$a =  new Rserve_REXP_Null();
				break;
				
			case self::XT_VECTOR: // generic vector
				$v = array();
				while ($i < $eoa) {
					$v[] = self::parseREXP($buf, $i);
				}
				
				$klass = 'Rserve_REXP_GenericVector';
				if( $class ) {
					if( in_array('data.frame', $class) ) {
						$klass = 'Rserve_REXP_Dataframe'; 
					}
				}
				$a = new $klass(); 
				$a->setValues($v);
				break;

			case self::XT_SYMNAME: // symbol
				$oi = $i;
				while ($i < $eoa && ord($r[$i]) != 0) {
					$i++;
				}
				$v =  substr($buf, $oi, $i - $oi);
				$a = new Rserve_REXP_Symbol();
				$a->setValue($v);
				break;
				
			case self::XT_LIST_NOTAG:
			case self::XT_LANG_NOTAG: // pairlist w/o tags
				$v = array();
				while ($i < $eoa) {
					$v[] = self::parseREXP($buf, $i);
				}
				$clasz = ($ra == self::XT_LIST_NOTAG) ? 'Rserve_REXP_List' : 'Rserve_REXP_Language';
				$a = new $clasz();
				$a->setValues($a);
				break;
			
			case self::XT_LIST_TAG:
			case self::XT_LANG_TAG: // pairlist with tags
				$clasz = ($ra == self::XT_LIST_TAG) ? 'Rserve_REXP_List' : 'Rserve_REXP_Language';
				$v = array();
				$names = array();
				while ($i < $eoa) {
					$v[] = self::parseREXP($buf, $i);
					$names[] = self::parseREXP($buf, $i);
				}
				$a = new $clasz();
				$a->setValues($v);
				$a->setNames($names);
				break;

			case self::XT_ARRAY_INT: // integer array
				$v = array();
				while ($i < $eoa) {
					$v[] = int32($r, $i);
					$i += 4;
				}
				$klass = 'Rserve_REXP_Integer';
				if( $class ) {
					if( in_array('factor', $class) ) {
						$klass = 'Rserve_REXP_Factor';
					}
				}
				$a = new $klass();
				$a->setValues($v);
				break;

			case self::XT_ARRAY_DOUBLE: // double array
				$v = array();
				while ($i < $eoa) {
					$v[] = flt64($r, $i);
					$i += 8;
				}
				$a = new Rserve_REXP_Double();
				$a->setValues($v);
				break;

			case self::XT_ARRAY_STR: // string array
				$v = array();
				$oi = $i;
				while ($i < $eoa) {
					if (ord($r[$i]) == 0) {
						$v[] = substr($r, $oi, $i - $oi);
						$oi = $i + 1;
					}
					$i++;
				}
				$a = new Rserve_REXP_String();
				$a->setValues($v);
				break;

			case self::XT_ARRAY_BOOL:  // boolean vector
				$n = int32($r, $i);
				$i += 4;
				$k = 0;
				$vv = array();
				while ($k < $n) {
					$v = int8($r, $i++);
					$vv[$k] = ($v == 1) ? TRUE : (($v == 0) ? FALSE : NULL);
					$k++;
				}
				$a = new Rserve_REXP_Logical();
				$a->setValues($vv);
				break;

			case self::XT_RAW: // raw vector
				$len = int32($r, $i);
				$i += 4;
				$v = substr($r, $i, $len);
				$a = new Rserve_REXP_Raw();
				$a->setValue($v);
				break;

			case self::XT_ARRAY_CPLX:
				$v = array();
				while ($i < $eoa) {
					$real = flt64($r, $i);
					$i += 8;
					$im = flt64($r, $i);
					$i += 8;
					$v[] = array($real, $im);	
				}
				$a = new Rserve_REXP_Complex();
				$a->setValues($v);
				break;
			/*		
		    case 48: // unimplemented type in Rserve
				$uit = int32($r, $i);
				// echo "Note: result contains type #$uit unsupported by Rserve.<br/>";
				$a = NULL;
				break;
            */
			default:
				// handle unknown type
                $a = new Rserve_REXP_Unknown($ra);
		}
		if( $attr && is_object($a) ) {
			$a->setAttributes($attr);
		}
		return $a;
	}

	public static function  xtName($xt) {
		switch($xt) {
			case self::XT_NULL:  return 'null';
			case self::XT_INT:  return 'int';
			case self::XT_STR:  return 'string';
			case self::XT_DOUBLE:  return 'real';
			case self::XT_BOOL:  return 'logical';
			case self::XT_ARRAY_INT:  return 'int*';
			case self::XT_ARRAY_STR:  return 'string*';
			case self::XT_ARRAY_DOUBLE:  return 'real*';
			case self::XT_ARRAY_BOOL:  return 'logical*';
			case self::XT_ARRAY_CPLX:  return 'complex*';
			case self::XT_SYM:  return 'symbol';
			case self::XT_SYMNAME:  return 'symname';
			case self::XT_LANG:  return 'lang';
			case self::XT_LIST:  return 'list';
			case self::XT_LIST_TAG:  return 'list+T';
			case self::XT_LIST_NOTAG:  return 'list/T';
			case self::XT_LANG_TAG:  return 'lang+T';
			case self::XT_LANG_NOTAG:  return 'lang/T';
			case self::XT_CLOS:  return 'clos';
			case self::XT_RAW:  return 'raw';
			case self::XT_S4:  return 'S4';
			case self::XT_VECTOR:  return 'vector';
			case self::XT_VECTOR_STR:  return 'string[]';
			case self::XT_VECTOR_EXP:  return 'expr[]';
			case self::XT_FACTOR:  return 'factor';
			case self::XT_UNKNOWN:  return 'unknown';
		}
		return '<? '.$xt.'>';
	}

	/**
	 *
	 * @param Rserve_REXP $value
     * This function is not functionnal. Please use it only for testing
	 */
	public static function createBinary(Rserve_REXP $value) {
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
				$contents .= mkint32($v);
				$o += 4;
				break;
			case self::XT_DOUBLE:
				$v = (float)$value->at(0);
				$contents .= mkfloat64($v);
				$o += 8;
				break;
			case self::XT_ARRAY_INT:
				$vv = $value->getValues();
				$n = count($vv);
				for($i = 0; $i < $n; ++$i) {
					$v = $vv[$i];
					$contents .= mkint32($v);
					$o += 4;
				}
				break;
			case self::XT_ARRAY_BOOL:
				$vv = $value->getValues();
				$n = count($vv);
				$contents .= mkint32($n);
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
					$contents .= mkfloat64($v);
					$o += 8;
				}
				break;
			case self::XT_RAW :
				$v = $value->getValue();
				$n = $value->length();
				$contents .= mkint32($n);
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
						$x = new Rserve_REXP_Null();
					}
					$iof = strlen($contents);
					$contents .= self::createBinary($x);
					if($type == XT_LIST_TAG || $type == XT_LANG_TAG) {
						$sym = new Rserve_REXP_Symbol();
						$sym->setValue($names[$i]);
						$contents .= self::createBinary($sym);
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
			$attr_off = self::createBinary($attr, $attr_bin, 0);
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
		$r .= mkint24($length);
		$r .= $contents;
		return $r;
	}
}

