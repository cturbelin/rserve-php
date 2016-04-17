<?php

namespace Sentiweb\Rserve;

/**
 * RServe Exception
 * @author Clément Turbelin
 *
 */
class Exception extends \Exception {

	public $packet;

	public function __construct($message, $packet=null) {
		parent::__construct($message);
		$this->packet = $packet;
	}

}