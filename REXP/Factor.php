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
* R Double Factor
* A factor is an integer value associated with a label (level in R vocabulary)
* Caution: first level is coded as a 1 value
*/
class Rserve_REXP_Factor extends Rserve_REXP_Integer {
	
	protected $levels;
	
	public function isFactor() { 
		return TRUE; 
	}
	
	/**
    * get levels
    */
    public function getLevels() {
		return $this->levels;
	}
	
	/**
    * Set levels
    */
    public function setLevels($levels) {
		$this->levels = $levels;
	}
	
	public function asCharacters() {
		$levels = $this->levels;
		$r = array();
		foreach($this->values as $v) {
			$r[] = $levels[$v];
		}
		return $r;
	}
	
	public function getType() {
		return Rserve_Parser::XT_FACTOR;
	}
	
	public function setAttributes(Rserve_REXP_List $attr) {
		parent::setAttributes($attr);
		$lev = $this->getAttribute('levels');
		if( $lev ) {
			$lev = $lev->getValues();
			$levels = array();
			$i = 0;
            foreach($lev as $l) {
				++$i;
                $levels[$i] =(string)$l; // force string for convinience
            }	
			$this->levels = $levels;
		}
	}
}