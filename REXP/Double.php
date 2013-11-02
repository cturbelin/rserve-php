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
* R Double vector
*/
class Rserve_REXP_Double extends Rserve_REXP_Vector {
	
	/**
	 * (non-PHPdoc)
	 * @see Rserve_REXP::isInteger()
	 */
	public function isInteger() { 
		return false; 
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Rserve_REXP::isNumeric()
	 */
	public function isNumeric() { 
		return true; 
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Rserve_REXP_Vector::getType()
	 */
	public function getType() {
		return Rserve_Parser::XT_ARRAY_DOUBLE;
	}
}