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
*/
class Rserve_REXP {

	/**
	 * List of attributes associated with the R object
	 * @var Rserve_REXP_List
	 */
	protected $attr = NULL;
	
	public function __construct() {
	}
	
	public function setAttributes(Rserve_REXP_List $attr) {
		$this->attr = $attr;
	}
	
	public function hasAttribute($name) {
		if( !$this->attr ) {
			return FALSE;
		}
	}
	
	public function getAttribute($name) {
		if( !$this->attr ) {
			return NULL;
		}
		return $this->attr->at($name);		
	}
	
	public function attr() {
		return $this->attr;
	}
	
	public function isVector() { 
		return FALSE; 
	}
	
	public function isInteger() { 
			return FALSE; 
	}
	
	public function isNumeric() { 
		return FALSE; 
	}
	
	public function isLogical() {
		 return FALSE; 
	}
	
	public function isString() { 
		return FALSE; 
	}
	
	public function isSymbol() { 
		return FALSE; 
	}
	
	public function isRaw() { 
		return FALSE; 
	}
	
	public function isList() { 
		return FALSE; 
	}
	
	public function isNull() { 
		return FALSE; 
	}
	
	public function isLanguage() { 
		return FALSE; 
	}
	
	public function isFactor() { 
		return FALSE; 
	}
	
	public function isExpression() { 
		return FALSE; 
	}
	
	public function toHTML() {
		return '<div class="rexp xt_'.$this->getType().'"><span class="typename">'.Rserve_Parser::xtName($this->getType()).'</span>'.$this->attrToHTML().'</div>';	
	}

	
	protected function attrToHTML() {
		if($this->attr) {
			return '<div class="attributes">'.$this->attr->toHTML().'</div>';
		}
	}
	
	public function getType() {
		return Rserve_Parser::XT_VECTOR;
	}
	
}