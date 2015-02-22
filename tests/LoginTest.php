<?php

/*
 * Refer to README for how to run this test
 */
	
require_once 'config.php';
require_once dirname(__FILE__).'/../Connection.php';

class LoginTest extends PHPUnit_Framework_TestCase {
 
	public function testLogin() {
	
		$cnx = new Rserve_Connection(RSERVE_HOST,RSERVE_PORT,
			array('username'=>RSERVE_USER,'password'=>RSERVE_PASS)
		);

		// random id
		$random = '';
		for($i = 0; $i < 10; ++$i) {
			$random .= dechex(mt_rand());
		}
		$random_id = uniqid($random, TRUE);
		
		$r = $cnx->evalString('x="'.$random_id.'"');
		
		$this->assertEquals($r, $random_id);

		$session = $cnx->detachSession();
	}
}
