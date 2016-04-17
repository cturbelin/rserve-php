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
use \Exception;

/**
 * R List implementation
 */
class RList extends Vector implements \ArrayAccess {

	protected $names = array();
	protected $is_named = false;

	public function setValues($values, $getNames = false) {
		$names = null;
		if( $getNames ) {
			$names = array_keys($values);
		}
		$values = array_values($values);
		parent::setValues($values);
		if($names) {
			$this->setNames($names);
		}
	}

	/**
	 * Set names
	 * @param unknown_type $names
	 */
	public function setNames($names) {
		if(count($this->values) != count($names)) {
			throw new \LengthException('Invalid names length');
		}
		$nn = array();
		foreach($names as $n) {
			$nn[] = (string)$n;
		}
		$this->names = $nn;
		$this->is_named = true;
	}

	/**
	 * return list of names
	 * @return array
	 */
	public function getNames() {
		return ($this->is_named) ? $this->names : array();
	}

	/**
	 * return true if the list is named
	 * @return bool
	 */
	public function isNamed() {
		return $this->is_named;
	}

	/**
	 * Get the value for a given name entry, if list is not named, get the indexed element
	 * @param string $name
	 * @return Rserve_REXP|mixed
	 */
	public function at($name) {
		if( $this->is_named ) {
			$i = array_search($name, $this->names);
			if($i < 0) {
				return null;
			}
			return $this->values[$i];
		}
	}

	/**
	 * Return element at the index $i
	 * @param int $i
	 * @return mixed Rserve_REXP or native value
	 */
	public function atIndex($i) {
		$i = (int)$i;
		$n = count($this->values);
		if( ($i < 0) || ($i >= $n) ) {
			throw new \OutOfBoundsException('Invalid index');
		}
		return $this->values[$i];
	}

	public function isList() { 
		return true; 
	}

	public function offsetExists($offset) {
		if($this->is_named) {
			return array_search($offset, $this->names) >= 0;
		} else {
			return isset($this->names[$offset]);
		}
	}

	public function offsetGet($offset) {
		return $this->at($offset);
	}

	public function offsetSet($offset, $value) {
		throw new Exception('assign not implemented');
	}

	public function offsetUnset($offset) {
		throw new Exception('unset not implemented');
	}

	public function getType() {
		if( $this->isNamed() ) {
			return Parser::XT_LIST_TAG;
		} else {
			return Parser::XT_LIST_NOTAG;
		}
	}

	public function toHTML() {
		$is_named = $this->is_named;
		$s = '<div class="rexp xt_'.$this->getType().'">';
		$n = $this->length();
		$s .= '<ul class="list"><span class="typename">List of '.$n.'</span>';
		for($i = 0; $i < $n; ++$i) {
			$s .= '<li>';
			$idx = ($is_named) ? $this->names[$i] : $i;
			$s .= '<div class="name">'.$idx.'</div>:<div class="value">';
			$v = $this->values[$i];
			if(is_object($v) AND ($v instanceof REXP)) {
				$s .= $v->toHTML();
			} else {
				$s .= (string)$v;
			}
			$s .= '</div>';
			$s .= '</li>';
		}
		$s .='</ul>';
		$s .= $this->attrToHTML();
		$s .= '</div>';
		return $s;
	}


}
