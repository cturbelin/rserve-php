<?php

require_once __DIR__ . '/config.php';

namespace Sentiweb\Rserve;
use Sentiweb\Rserve\Parser\REXP as Parser_REXP;

class REXPTest extends \PHPUnit_Framework_TestCase {


	private function create_REXP($values, $type, $options=array()) {
		$cn = 'Sentiweb\\Rserve\\REXP\\'.$type;
		$r = new $cn();
		if(is_subclass_of($r, 'Sentiweb\\Rserve\\REXP\\Vector')) {
			if( is_subclass_of($r,'Sentiweb\\Rserve\\REXP\\RList') AND @$options['named']) {
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
			array('RString', array('toto','Lorem ipsum dolor sit amet','')),
		);
	}


	/**
	* @dataProvider providerTestParser
	 * @param unknown_type $type
	 * @param unknown_type $values
	 */
	public function testParser($type, $values) {
		
		$serializer = new Serializer();
		
		
		$rexp = $this->create_REXP($values, $type);

		$bin = $serializer->serialize($rexp);

		$i = 0; // No offset
		
		$parser = new Parser_REXP();
		$r2 = $parser->parse($bin, $i);

		$this->assertEquals( get_class($rexp), get_class($r2));

		$this->assertEquals( $rexp->getValues(), $r2->getValues());

	}
}
