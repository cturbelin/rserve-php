<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Connection.php';

class SessionTest extends PHPUnit_Framework_TestCase {


	private function create_REXP($values, $type, $options=array()) {
		$cn = 'Rserve_REXP_'.$type;
		$r = new $cn();
		if(is_subclass_of($r, 'Rserve_REXP_Vector')) {
			if( is_subclass_of($r,'Rserve_REXP_List') AND @$options['named']) {
				$r->setValues($values, TRUE);
			} else {
				$r->setValues($values);
			}
		} else {
			$r->setValue($values);
		}
		return $r;
	}

	public function providerTestParser() {
		return array(
			array('Integer', array(1, 3, 7, 1129, 231923,22)),
			array('Double', array(1.234, 3.432, 4.283, M_PI)),
			array('Logical', array(TRUE, FALSE, TRUE, TRUE, FALSE, NULL)),
			array('String', array('toto','Lorem ipsum dolor sit amet','')),
		);
	}


	/**
	* @dataProvider providerTestParser
	 * @param unknown_type $type
	 * @param unknown_type $values
	 */
	public function testParser($type, $values) {
		$rexp = $this->create_REXP($values, $type);

		$bin = Rserve_Parser::createBinary($rexp);

		$i = 0; // No offset
		
		$r2 = Rserve_Parser::parseREXP($bin, $i);

		$this->assertEquals( get_class($rexp), get_class($r2));

		$this->assertEquals( $rexp->getValues(), $r2->getValues());

	}
}
