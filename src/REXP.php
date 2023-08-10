<?php

namespace Sentiweb\Rserve;

/**
* Rserve client for PHP
* Supports Rserve protocol 0103 only (used by Rserve 0.5 and higher)
* @author Clément TURBELIN
* Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
* This code is inspired from Java client for Rserve (Rserve package v0.6.2) developped by Simon Urbanek(c)
*/

/**
* R Expression wrapper
*
* Each R structure returned by Rserve are wrapped in an REXP class regarding its type (@see Rserve_Parser::xtName())
*
*/

use Sentiweb\Rserve\REXP\RList;
use Sentiweb\Rserve\REXP\Vector;

class REXP {

	/**
	 * List of attributes associated with the R object
	 * @var RList
	 */
	protected $attr = null;

	public function __construct() {
	}

	/**
	 * Set attributes for this REXP structure
	 * @param RList $attr
	 */
	public function setAttributes(RList $attr) {
		$this->attr = $attr;
	}

	/**
	 * Check if an attribute exists for a given name
	 * @param REXP $name
	 * @return bool
	 */
	public function hasAttribute($name):bool {
		if( !$this->attr ) {
			return false;
		}
		return true;
	}

	/**
	 * Get an attribute
	 * @param REXP $name
	 * @return REXP
	 */
	public function getAttribute($name) {
		if( !$this->attr ) {
			return null;
		}
		return $this->attr->at($name);
	}

	/**
	 * get attributes for this REXP
	 * @return RList|null
	 */
	public function attributes() {
		return $this->attr;
	}

	/**
	 * Is a vector (list of indexed values whatever it's type)
	 * @return bool
	 */
	public function isVector():bool {
		return false;
	}

	/**
	 * Is an Integer vector
	 * @return bool
	 */
	public function isInteger():bool {
			return false;
	}

	/**
	 * Is a numeric vector (Double)
	 * @return bool
	 */
	public function isNumeric():bool {
		return false;
	}

	/**
	 * Is a logical vector
	 * @return bool
	 */
	public function isLogical():bool {
		 return false;
	}

	/**
	 * Is a string vector
	 * @return bool
	 */
	public function isString():bool {
		return false;
	}

	/**
	 * Is a symbol vector
	 * @return bool
	 */
	public function isSymbol():bool {
		return false;
	}

	/**
	 * Is a raw vector (binary)
	 * @return bool
	 */
	public function isRaw():bool {
		return false;
	}

	/**
	 * Is a list (Rserve_Rexp_List)
	 * @return bool
	 */
	public function isList():bool {
		return false;
	}

	/**
	 * Is a null value
	 * @return bool
	 */
	public function isNull():bool {
		return false;
	}

	/**
	 * Is a language expression
	 * @return bool
	 */
	public function isLanguage():bool {
		return false;
	}

	/**
	 * Is a factor vector
	 * @return bool
	 */
	public function isFactor():bool {
		return false;
	}

	/**
	 * Is an expression
	 * @return bool
	 */
	public function isExpression():bool {
		return false;
	}

	/**
	 * object content's length
	 * @return int
	 */
	public function length():int {
		return 0;
	}

	/**
	 * Return R class
	 * @return string
	 */
	public function getClass():string {
		$class = $this->getAttribute('class');
		if($class) {
			if(!$class instanceof Vector) {
				throw new Exception("Class attribute must be a vector");
			}
			return $class->getValues();
		}
		$type = $this->getType();
		switch($type) {
			case Parser::XT_ARRAY_BOOL:
				$class = 'logical';
				break;
			case Parser::XT_ARRAY_INT:
				$class = 'integer';
				break;
			case Parser::XT_ARRAY_DOUBLE:
				$class = 'numeric';
				break;
			case Parser::XT_ARRAY_STR:
				$class ='character';
				break;
			case Parser::XT_FACTOR:
				$class = 'factor';
				break;
			default:
				$class = 'unknown';
		}
		return $class;
	}

	/**
	 * Get an HTML representation of the object
	 * For debugging purpose
	 */
	public function toHTML() {
		return '<div class="rexp xt_'.$this->getType().'"><span class="typename">'.Parser::xtName($this->getType()).'</span>'.$this->attrToHTML().'</div>';
	}

	protected function attrToHTML() {
		if($this->attr) {
			return '<div class="attributes">'.$this->attr->toHTML().'</div>';
		}
	}

	/**
	 * Get R Type (@see Rserve_Parser)
	 * @return int
	 */
	public function getType() {
		return Parser::XT_VECTOR;
	}

}
