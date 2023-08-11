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
* R Vector
*/
class Vector extends REXP {
	
	protected array $values;
	
	public function __construct() {
		$this->values = [];
	}
	
	/**
	 * return int
	 */
	public function length():int {
		return( count($this->values) );
	}
	
	public function isVector():bool {
		return true;
	}
	
	public function setValues($values) {
		$this->values = $values;
	}
	
	public function getValues() {
		return $this->values;
	}
	
	/**
	 * Return dimensions length of the vector
	 * uses 'dim' attribute if exists or the length of the vector (one dimension vector)
	 */
	public function dim() {
		/**
		 * @var Vector
		 */
		$dim = $this->getAttribute('dim');
		if( $dim ) {
			return $dim->getValues();
		}
		return [$this->length()]; 
	}
	
	/**
	 * Matrix is a multidimensionnal vector 
	 */
	public function isMatrix() {
		$dim = $this->dim();
		return count($dim) > 1;
	}
	
	/**
	 * Get value 
	 * @param unknown_type $index
	 */
	public function at($index) {
		return $this->values[$index] ?? null;
	}
	
	public function getType() {
		return Parser::XT_VECTOR;
	}
	
	public function toHTML() {
		$s = '<div class="rexp vector xt_'.$this->getType().'">';
		$dim = $this->dim();
		$n = $this->length();
		$s .= '<span class="typename">'.Parser::xtName($this->getType()).'</span>';
		$s .= '[';
		$s .= join(',', $dim);
		$s .= ']';
		$s .= '<div class="values">';
		if($n) {
			$m = ($n > 20) ? 20 : $n;
			for($i = 0; $i < $m; ++$i) {
				$v = $this->values[$i];
				if(is_object($v) AND ($v instanceof REXP)) {
					$v = $v->toHTML();
				} else {
					$v = $this->valueToHTML($v);
				}
				$s .= '<div class="value">'.$v.'</div>';
			}
		}
		$s .= '</div>';
		$s .= $this->attrToHTML();
		$s .= '</div>';
		return $s;
	}
	
	/**
	 * HTML representation for a single value of the vector
	 * @param mixed $v
	 */
	protected function valueToHTML($v) {
		return (string)$v;
	}
}
