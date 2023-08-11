<?php
namespace Sentiweb\Rserve\REXP;

/**
 * Complex values
 * Each complex number is stored in an array(real part, imaginary part)
 * @author Clément Turbelin
 *
 */
class Complex extends Vector {

	protected function getCplx($index, $part) {
		if( !is_null($index) ) {
			$v = $this->at($index);
			if( is_array($v) ) {
				return $v[$part];
			}
			return null;
		}
		$r = [];
		foreach($this->values as $v) {
			$r[] = $v[$part];
		}
		return $r;
	}

	/**
	* Get imaginary part of vector
	* @param int index of vector
	* @return float
	*/
	public function getImaginary($index = null) {
		return $this->getCplx($index, 1);
	}

	/**
	* Get real part of vector
	* @param int index of vector
	* @return float
	*/
	public function getReal($index = null) {
		return $this->getCplx($index, 0);
	}

	/**
	 * (non-PHPdoc)
	 * @see Rserve_REXP_Vector::valueToHTML()
	 */
	protected function valueToHTML($v) {
		return $v[0]+' + '.$v[1].'i';
	}

}
