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
class Rserve_REXP_Vector extends Rserve_REXP {
	
	protected $values;
	
	public function __construct() {
		$this->values = array();
	}
	
	/**
	 * return int
	 */
	public function length() {
		return( count($this->values) );
	}
	
	public function isVector() {
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
		$dim = $this->getAttribute('dim');
		if( $dim ) {
			return $dim->getValues();
		}
		return array($this->length()); 
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
		return isset($this->values[$index]) ? $this->values[$index] : null;
	}
	
	public function getType() {
		return Rserve_Parser::XT_VECTOR;
	}
	
	public function toHTML() {
		$s = '<div class="rexp vector xt_'.$this->getType().'">';
		$dim = $this->dim();
		$n = $this->length();
		$s .= '<span class="typename">'.Rserve_Parser::xtName($this->getType()).'</span>';
		$s .= '[';
		$s .= join(',', $dim);
		$s .= ']';
		$s .= '<div class="values">';
		if($n) {
			$m = ($n > 20) ? 20 : $n;
			for($i = 0; $i < $m; ++$i) {
				$v = $this->values[$i];
				if(is_object($v) AND ($v instanceof Rserve_REXP)) {
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
