<?php

class Rserve_REXP_Dataframe extends Rserve_REXP_Vector {
	
	public function getNames() {
		$n =  $this->getAttribute('names');
		if($n) {
			return $n->getValues();
		}
		return NULL;
	}
	
	public function getRowNames() {
		$n  = $this->getAttribute('row.names');
		if($n) {
			return $n->getValues();
		}
		return NULL;
	}
	
	/**
	 * Number of rows
	 */
	public function nrows() {
		
	}  
}