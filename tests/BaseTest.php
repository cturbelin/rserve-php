<?php
namespace Sentiweb\Rserve\Tests;

/*
 * Refer to README for how to run this test
 */

use PHPUnit\Framework\TestCase;

use Sentiweb\Rserve\Connection;

class BaseTest extends TestCase {
    
    protected ConnectionManager $connectionManager;

	/**
	 *
	 */
	protected function setUp():void {
        $this->connectionManager = new ConnectionManager();
	}

    protected function getConnection($withAuth=false): ?Connection {
        return $this->connectionManager->create($withAuth);
    }

    /**
     * Create a reasonably random string (only for testing, no need for secure crypto)
     *
     * @return string
     */
    protected function getRandomString():string {
        // random id
		$random = '';
		for ($i = 0; $i < 10; ++$i) {
			$random .= dechex(mt_rand());
		}
		return uniqid($random, true);
    }
}