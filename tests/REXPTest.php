<?php

namespace Sentiweb\Rserve;

use PHPUnit\Framework\TestCase;


use Sentiweb\Rserve\Serializer;
use Sentiweb\Rserve\Parser\REXP as Parser_REXP;
use Sentiweb\Rserve\REXP\Vector;
use Sentiweb\Rserve\REXP\RList;

class REXPTest extends TestCase
{

	private function create_REXP($values, $type, $options = [])
	{
		$cn = 'Sentiweb\\Rserve\\REXP\\' . $type;
		$r = new $cn();
		if (is_subclass_of($r, Vector::class)) {
			$named = $options['named'] ?? false;
			if (is_subclass_of($r, RList::class) && $named) {
				$r->setValues($values, true);
			} else {
				$r->setValues($values);
			}
		} else {
			$r->setValue($values);
		}
		return $r;
	}

	public function providerTestParser()
	{
		return [
			['Integer', [1, 3, 7, 1129, 231923, 22]], 
			['Double', [1.234, 3.432, 4.283, M_PI]], 
			['Logical', [true, false, true, true, false, null]], 
			['RString', ['toto', 'Lorem ipsum dolor sit amet', '']]
		];
	}


	/**
	 * @dataProvider providerTestParser
	 * @param string $type
	 * @param mixed $values
	 */
	public function testParser($type, $values)
	{

		$serializer = new Serializer();


		$rexp = $this->create_REXP($values, $type);

		$bin = $serializer->serialize($rexp);

		$i = 0; // No offset

		$parser = new Parser_REXP();
		$r2 = $parser->parse($bin, $i);

		$this->assertEquals(get_class($rexp), get_class($r2));

		$this->assertEquals($rexp->getValues(), $r2->getValues());
	}
}
