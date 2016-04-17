<?php

require_once __DIR__ . '/config.php';

use Sentiweb\Rserve\Connection;

class SessionTest extends PHPUnit_Framework_TestCase {


	public function testSession() {
		$cnx = new Connection(RSERVE_HOST);

		// random id
		$random = '';
		for($i = 0; $i < 10; ++$i) {
			$random .= dechex(mt_rand());
		}
		$random_id = uniqid($random, TRUE);

		$r = $cnx->evalString('x="'.$random_id.'"');

		$this->assertEquals($r, $random_id);

		$session = $cnx->detachSession();

		$cnx = new Connection($session);

		$x = $cnx->evalString('x');

		$this->assertEquals($x, $random_id);

	}
}
