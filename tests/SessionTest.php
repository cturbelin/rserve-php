<?php

namespace Sentiweb\Rserve;

use PHPUnit\Framework\TestCase;
use Sentiweb\Rserve\Tests\BaseTest;

class SessionTest extends BaseTest
{

	public function testSession()
	{
		$cnx = $this->getConnection();
		if(!$cnx) {
			$this->markTestSkipped('skipping connection aware tests');
			return;
		}

		$random_id = $this->getRandomString();

		$r = $cnx->evalString('x="' . $random_id . '"');

		$this->assertEquals($r, $random_id);

		$session = $cnx->detachSession();

		$cnx = new Connection($session);

		$x = $cnx->evalString('x');

		$this->assertEquals($x, $random_id);
	}
}
