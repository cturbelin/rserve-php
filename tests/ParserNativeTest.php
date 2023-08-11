<?php

namespace Sentiweb\Rserve\Tests;

use Exception;

class ParserNativeTest extends BaseTest
{

	/**
	 * Provider for test cases
	 */
	function providerSimpleTests()
	{
		return Definition::$native_tests;
	}

	/**
	 * @dataProvider providerSimpleTests
	 * @param string $cmd R command
	 * @param string $type expected type
	 * @param array $expected expected php structure
	 * @param array $filters filters to apply to the R result to fit the tests values, each filter is array(funcname, param1,...), or a string funcname|param1|param2...
	 * @covers Rserve_Parser::parse
	 * @covers Rserve_Connection::evalString
	 */
	public function testSimpleTypes($cmd, $type, $expected, $filters = null)
	{

		$cnx = $this->getConnection();
		if (!$cnx) {
			$this->markTestSkipped('skipping connection aware tests');
			return;
		}

		$r = $cnx->evalString($cmd);
		if (is_array($expected)) {
			$this->assertIsArray($r);
			if (is_array($r) && !is_null($type)) {
				foreach ($r as $x) {
					$this->assertIsType($type, $x);
				}
			}
		} else {
			if (!is_null($type)) {
				$this->assertIsType($type, $r);
			}
		}
		if (!is_null($filters)) {
			foreach ($filters as $key => $filter) {
				if (is_string($filter)) {
					$filter = explode('|', $filter);
				}
				$f = array_shift($filter);
				if (!is_callable($f)) {
					throw new Exception('Bad filter ' . $f . ' for ' . $key);
				}
				$params = array_merge([$r[$key]], $filter);
				$r[$key] = call_user_func_array($f, $params);
			}
		}
		$this->assertEquals($r, $expected);
	}

	protected function assertIsType($type, $value)
	{
		switch ($type) {
			case Definition::TYPE_BOOL:
				$this->assertIsBool($value);
				break;
			case Definition::TYPE_INT:
				$this->assertIsInt($value);
				break;
			case Definition::TYPE_FLOAT:
				$this->assertIsFloat($value);
				break;

			case Definition::TYPE_STRING:
				$this->assertIsString($value);
				break;

			default:
				throw new Exception("Unknown type '$type'");
		}
	}
}
