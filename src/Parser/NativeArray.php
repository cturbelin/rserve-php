<?php
namespace Sentiweb\Rserve\Parser;

use Sentiweb\Rserve\Parser;
use Sentiweb\Rserve\ArrayWrapper;


class NativeArray extends Parser {
	
	protected $use_wrapper = false;
	
	protected $factor_as_string;
	
	public function __construct($options=[]) {
		$this->use_wrapper = isset($options['wrapper']) ? $options['wrapper'] : false;
		$this->factor_as_string = isset($options['factor_as_string']) ? (bool)$options['factor_as_string'] : true;
	}
	
	/**
	 * SEXP to php array parser
	 * parse SEXP results -- limited implementation for now (large packets and some data types are not supported)
	 * @param string $buf
	 * @param int $offset
	 * @return native php array or a RNative object if if the static property $use_array_object is TRUE
	 */
	public function parse($buf, &$offset) {
		$attr = NULL;
		$r = $buf;
		$i = $offset;
	
		// some simple parsing - just skip attributes and assume short responses
		$ra = _rserve_int8($r, $i);
		$rl = _rserve_int24($r, $i + 1);
		$i += 4;
	
		$offset = $eoa = $i + $rl;
		//echo '[ '.self::xtName($ra & 63).', length '.$rl.' ['.$i.' - '.$eoa.']<br/>';
		if (($ra & 64) == 64) {
			throw new Exception('long packets are not supported (yet).');
		}
		if ($ra > self::XT_HAS_ATTR) {
			//echo '(ATTR*[';
			$ra &= ~self::XT_HAS_ATTR;
			$al = _rserve_int24($r, $i + 1);
			$tmp = $i; // use temporary to protect current offset
			$attr = $this->parse($buf, $tmp);
			//echo '])';
			$i += $al + 4;
		}
		switch($ra) {
			case self::XT_NULL:
				$a = null;
				break;
			case self::XT_VECTOR: // generic vector
				$a = [];
				while ($i < $eoa) {
					$a[] = $this->parse($buf, $i);
				}
				
				// if the 'names' attribute is set, convert the plain array into a map
				if ( isset($attr['names']) ) {
					$names = $attr['names'];
					if(is_string($names)) {
						$names = [$names];
					}
					$a = array_combine($names, $a);
					
				}
				break;
	
			case self::XT_INT:
				$a = _rserve_int32($r, $i);
				$i += 4;
				break;
	
			case self::XT_DOUBLE:
				$a = _rserve_flt64($r, $i);
				$i += 8;
				break;
	
			case self::XT_BOOL:
				$v = _rserve_int8($r, $i++);
				$a = ($v == 1) ? true : (($v == 0) ? false : null);
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
				$a = [];
				while ($i < $eoa) {
					$a[] = $this->parse($buf, $i);
				}
				break;
	
			case self::XT_LIST_TAG:
			case self::XT_LANG_TAG:
				// pairlist with tags
				$a = [];
				while ($i < $eoa) {
					$val = $this->parse($buf, $i);
					$tag = $this->parse($buf, $i);
					$a[$tag] = $val;
				}
				break;
	
			case self::XT_ARRAY_INT: // integer array
				$a = [];
				while ($i < $eoa) {
					$a[] = _rserve_int32($r, $i);
					$i += 4;
				}
				$n = count($a);
				if ($n == 1) {
					$a = $a[0];
				}
				
				// If factor, then transform to characters
				if( $this->factor_as_string  && isset($attr['class']) ) {
					$c = $attr['class'];
					$is_factor = is_string($c) && ($c == 'factor');
					if($is_factor) {
						$levels = $attr['levels'];
						if($n == 1) {
							if($a < 0) {
								$a = null;
							} else {
                                if (is_array($levels)) {
                                    $a = $levels[ $a ];
                                } else {
                                    // Only one levels & current value is first level, ok
                                    if ($a == 1) {
                                        $a = $levels;
                                    }
                                }
                            }
						} else {
							for ($k = 0; $k < $n; ++$k) {
								$i = $a[$k];
								if ($i < 0) {
									$a[$k] = null;
								} else {
									$a[$k] = $levels[ $i -1];
								}
							}
                        }	
					}
				}
				break;
	
			case self::XT_ARRAY_DOUBLE:// double array
				$a = [];
				while ($i < $eoa) {
					$a[] = _rserve_flt64($r, $i);
					$i += 8;
				}
				if (count($a) == 1) {
					$a = $a[0];
				}
				break;
	
			case self::XT_ARRAY_STR: // string array
				$a = [];
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
				$n = _rserve_int32($r, $i);
				$i += 4;
				$k = 0;
				$a = [];
				while ($k < $n) {
					$v = _rserve_int8($r, $i++);
					$a[$k++] = ($v == 1) ? true : (($v == 0) ? false : null);
				}
				if ($n == 1) {
					$a =  $a[0];
				}
				break;
	
			case self::XT_RAW: // raw vector
				$len = _rserve_int32($r, $i);
				$i += 4;
				$a =  substr($r, $i, $len);
				break;
	
			case self::XT_ARRAY_CPLX:
				// real part
				$real = [];
				$im = [];
				while ($i < $eoa) {
					$real[] = _rserve_flt64($r, $i);
					$i += 8;
					$im[] = _rserve_flt64($r, $i);
					$i += 8;
				}
				if (count($real) == 1) {
					$a = [$real[0], $im[0]];
				} else {
					$a = [$real, $im];
				}
				break;
	
			case 48: // unimplemented type in Rserve
				$uit = _rserve_int32($r, $i);
				// echo "Note: result contains type #$uit unsupported by Rserve.<br/>";
				$a = null;
				break;
	
			default:
				echo 'Warning: type '.$ra.' is currently not implemented in the PHP client.';
				$a = null;
		} // end switch
	
		if( $this->use_wrapper ) {
			if( is_array($a) && $attr) {
				return new ArrayWrapper($a, $attr, $ra);
			} else {
				return $a;
			}
		}
		return $a;
	}
	
}