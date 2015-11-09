<?php
/**
* Rserve client for PHP
* Supports Rserve protocol 0103 only (used by Rserve 0.5 and higher)
* $Revision$
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

class Rserve_REXP {

	/**
	 * List of attributes associated with the R object
	 * @var Rserve_REXP_List
	 */
	protected $attr = NULL;
	
	public function __construct() {
	}
	
	/**
	 * Set attributes for this REXP structure
	 * @param Rserve_REXP_List $attr
	 */
	public function setAttributes(Rserve_REXP_List $attr) {
		$this->attr = $attr;
	}
	
	/**
	 * Check if an attribute exists for a given name
	 * @param Rserve_REXP $name
	 * @return bool
	 */
	public function hasAttribute($name) {
		if( !$this->attr ) {
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Get an attribute
	 * @param Rserve_REXP $name
	 * @return Rserve_REXP
	 */
	public function getAttribute($name) {
		if( !$this->attr ) {
			return NULL;
		}
		return $this->attr->at($name);		
	}
	
	/**
	 * get attributes for this REXP
	 * @return Rserve_REXP_List
	 */
	public function attributes() {
		return $this->attr;
	}
	
	/**
	 * Is a vector (list of indexed values whatever it's type)
	 * @return bool
	 */
	public function isVector() { 
		return FALSE; 
	}
	
	/**
	 * Is an Integer vector 
	 * @return bool
	 */
	public function isInteger() { 
			return FALSE; 
	}
	
	/**
	 * Is a numeric vector (Double)
	 * @return bool
	 */
	public function isNumeric() { 
		return FALSE; 
	}
	
	/**
	 * Is a logical vector
	 * @return bool
	 */
	public function isLogical() {
		 return FALSE; 
	}
	
	/**
	 * Is a string vector
	 * @return bool
	 */
	public function isString() { 
		return FALSE; 
	}
	
	/**
	 * Is a symbol vector
	 * @return bool
	 */
	public function isSymbol() { 
		return FALSE; 
	}
	
	/**
	 * Is a raw vector (binary)
	 * @return bool
	 */
	public function isRaw() { 
		return FALSE; 
	}
	
	/**
	 * Is a list (Rserve_Rexp_List)
	 * @return bool
	 */
	public function isList() { 
		return FALSE; 
	}
	
	/**
	 * Is a null value
	 * @return bool
	 */
	public function isNull() { 
		return FALSE; 
	}
	
	/**
	 * Is a language expression
	 * @return bool
	 */
	public function isLanguage() { 
		return FALSE; 
	}
	
	/**
	 * Is a factor vector
	 * @return bool
	 */
	public function isFactor() { 
		return FALSE; 
	}
	
	/**
	 * Is an expression
	 * @return bool
	 */
	public function isExpression() { 
		return FALSE; 
	}
	
	/**
	 * object content's length
	 * @return int
	 */
	public function length() {
        return 0;
    }
    
	/**
	 * Return R class 
	 * @return string
	 */
	public function getClass() {
		$class = $this->getAttribute('class');
		if($class) {
			return $class->getValues();
		}
		$type = $this->getType();
		switch($type) {
			case Rserve_Parser::XT_ARRAY_BOOL:
				$class = 'logical';
				break;
			case Rserve_Parser::XT_ARRAY_INT:
				$class = 'integer';
				break;
			case Rserve_Parser::XT_ARRAY_DOUBLE:
				$class = 'numeric';
				break;
			case Rserve_Parser::XT_ARRAY_STR:
				$class ='character';
				break;
			case Rserve_Parser::XT_FACTOR:
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
		return '<div class="rexp xt_'.$this->getType().'"><span class="typename">'.Rserve_Parser::xtName($this->getType()).'</span>'.$this->attrToHTML().'</div>';	
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
		return Rserve_Parser::XT_VECTOR;
	}
	
}
