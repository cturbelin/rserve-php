<?php

namespace Sentiweb\Rserve;

class Protocol {
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
	
}