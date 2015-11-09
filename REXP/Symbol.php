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
* R symbol element
*/
class Rserve_REXP_Symbol extends Rserve_REXP {

	protected $name;

	public function setValue($value) {
		$this->name = $value;
	}

	public function getValue() {
		return $this->name;
	}

	public function isSymbol() { return TRUE; }

	public function getType() {
		return Rserve_Parser::XT_SYM;
	}

	public function toHTML() {
	 return '<div class="rexp xt_'.$this->getType().'"><span class="typename">'.Rserve_Parser::xtName($this->getType()).'</span>'.$this->name.$this->attrToHTML().'</div>';
	}

	public function __toString() {
		return $this->name;
	}

	public function length() {
		return 1;
	}

}
