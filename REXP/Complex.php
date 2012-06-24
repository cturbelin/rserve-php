<?php

/**
 * Complex values
 * Each complex number is stored in an array(real part, imaginary part)
 * @author Clément Turbelin
 *
 */
class Rserve_REXP_Complex extends Rserve_REXP_Vector {
	
	
	private function getCplx($i, $part) {
		if( !is_null($i) ) {
			$v = $this->at($i);
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
	
	public function getImaginary($i = null) {
		return $this->getCplx($i, 1);
	}
    
	public function getReal($i = null) {
		return $this->getCplx($i, 0);
	}
    
	protected function valueToHTML($v) {
		return $v[0]+' + '.$v[1].'i';
	}
    
}


