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
* R Raw data
*/
class Rserve_REXP_Raw extends Rserve_REXP {
	
	protected $value;
	
	/**
	 * return int
	 */
	public function length() {
		return strlen($value);
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getValue($value) {
		return $this->value;
	}
	
	public function  isRaw() { return TRUE; }
	
	public function getType() {
		return Rserve_Parser::XT_RAW;
	}
	
	public function toHTML() {
		$s = strlen($this->value) > 60 ? substr($this->value,0,60).' (truncated)': $this->value;
		return '<div class="rexp xt_'.$this->getType().'"> <span class="typename">raw</span><div class="value">'.$s.'</div>'.$this->attrToHTML().'</div>';	
	}
	
}
