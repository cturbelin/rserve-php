<?php

/**
 * Complex values
 * Each complex number is stored in an array(real part, imaginary part)
 * @author Clément Turbelin
 *
 */
class Rserve_REXP_Complex extends Rserve_REXP_Vector {
	
	
	private function getCplx($index, $part) {
		if( !is_null($index) ) {
			$v = $this->at($index);
			if( is_array($v) ) {
				return $v[$part];
			}
			return null;
		}
		$r = array();
		foreach($this->values as $v) {
			$r[] = $v[$part];
		}
		return $r;
	}
	
	/**
	* Get imaginary part of vector
	* @param int index of vector
	*/
	public function getImaginary($index = null) {
		return $this->getCplx($index, 1);
	}
    
	/**
	* Get real part of vector
	* @param int index of vector
	*/
	public function getReal($index = null) {
		return $this->getCplx($index, 0);
	}
    
	protected function valueToHTML($v) {
		return $v[0]+' + '.$v[1].'i';
	}
    
}


