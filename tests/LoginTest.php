<?php

namespace Sentiweb\Rserve\Tests;

/*
 * Refer to README for how to run this test
 */

use PHPUnit\Framework\TestCase;

use Sentiweb\Rserve\Connection;

class LoginTest extends BaseTest
{

	public function testLogin()
	{

		$cnx = $this->getConnection(true);

		if(!$cnx) {
			$this->markTestSkipped('skipping authenticated connection aware tests');
			return;
		}

		$random_id = $this->getRandomString();

		$r = $cnx->evalString('x="' . $random_id . '"');

		$this->assertEquals($r, $random_id);
	}
}
