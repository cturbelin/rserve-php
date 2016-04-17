<?php

namespace Sentiweb\Rserve;

/**
 * R Session wrapper
 * @author Clément Turbelin
 *
 */
class Session {

	/**
	 * Session key
	 * @var string
	 */
	public $key;

	/**
	 *
	 * @var int
	 */
	public $port;

	public $host;

	public function __construct($key, $host, $port) {
		$this->key = $key;
		$this->port = $port;
		$this->host = $host;
	}

	public function __toString() {
		$k = base64_encode($this->key);
		return sprintf('Session %s:%d identified by base64:%s', $this->host, $this->port, $k);
	}

}
