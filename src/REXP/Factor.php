<?php
/**
* Rserve client for PHP
* Supports Rserve protocol 0103 only (used by Rserve 0.5 and higher)
* $Revision$
* @author Clément TURBELIN
* Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
* This code is inspired from Java client for Rserve (Rserve package v0.6.2) developped by Simon Urbanek(c)
*/
namespace Sentiweb\Rserve\REXP;

use Sentiweb\Rserve\REXP;
use Sentiweb\Rserve\Parser;
/**
* R Double Factor
* A factor is an integer value associated with a label (level in R vocabulary)
* Caution: first level is coded as a 1 value
*/
class Factor extends Integer {

	protected $levels;

	public function isFactor() {
		return true;
	}

	/**
	 * get levels
	 * @return string
	 */
	public function getLevels() {
		return $this->levels;
	}

	/**
	 * Set levels from
	 */
	public function setLevels($levels) {
		if($levels instanceof RString) {
			$levels = $levels->getValues();
		}
		$this->levels = $levels;
	}

	/**
	 * Convert an levels encoded vector to a character vector
	 * @return Rserve_REXP
	 */
	public function asCharacter() {
		$levels = $this->levels;
		$r = array();
		foreach($this->values as $v) {
			$r[] = $levels[$v];
		}
		$rexp = new RString();
		$rexp->setValues($r);
		return $rexp;
	}

	public function getType() {
		return Parser::XT_FACTOR;
	}

	public function setAttributes(RList $attr) {
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
