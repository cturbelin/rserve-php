<?php
namespace Sentiweb\Rserve\REXP;

use Sentiweb\Rserve\REXP;
use Sentiweb\Rserve\Parser;

class Dataframe extends Vector {

	/**
	 * R names() => columns names
	 * @return array()
	 */
	public function getNames() {
		$n =  $this->getAttribute('names');
		if($n) {
			return $n->getValues();
		}
		return NULL;
	}

	/**
	 * R rownames()
	 * @return array()
	 */
	public function getRowNames() {
		$n  = $this->getAttribute('row.names');
		if($n) {
			return $n->getValues();
		}
		return NULL;
	}

	/**
	 * Number of rows
	 * @return int
	 */
	public function nrow() {
		$v = $this->getValues();
		if( is_array($v) ) {
			$v = $v[0];
			return $v->length();
		}
		return 0;
	}

	/**
	 * Number of columns
	 * @return int
	 */
	public function ncol() {
		return $this->length();
	}

}
