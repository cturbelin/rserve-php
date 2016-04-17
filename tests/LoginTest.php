<?php

/*
 * Refer to README for how to run this test
 */

require_once __DIR__ . '/config.php';

use Sentiweb\Rserve\Connection;

class LoginTest extends PHPUnit_Framework_TestCase {

	public function testLogin() {
		
		if(!(defined('RSERVE_USER') && defined('RSERVE_PASS')) ) {
			$this->markTestSkipped('login configuration not defined.');
			return;
		}

		$params = array('username'=>RSERVE_USER,'password'=>RSERVE_PASS);
		
		$cnx = new Connection(RSERVE_HOST, RSERVE_PORT, $params);

		// random id
		$random = '';
		for($i = 0; $i < 10; ++$i) {
			$random .= dechex(mt_rand());
		}
		$random_id = uniqid($random, TRUE);

		$r = $cnx->evalString('x="'.$random_id.'"');

		$this->assertEquals($r, $random_id);

	}
}
