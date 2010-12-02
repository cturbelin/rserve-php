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
*/
class Rserve_REXP_Factor extends Rserve_REXP_Integer {
	
	protected $levels;
	
	public function isFactor() { return TRUE; }
	
	public function getLevels() {
		return $this->levels;
	}
	
	public function setLevels($levels) {
		$this->levels = $levels;
	}
	
	public function asCharacters() {
		$l = $this->levels;
		foreach($this->values as $v) {
			
		}	
	}
	
	public function getType() {
		return Rserve_Parser::XT_FACTOR;
	}
	
}