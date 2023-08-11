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
}