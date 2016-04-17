<?php

namespace Sentiweb\Rserve;

/**
 * Rserve message Parser
 * @author Clément Turbelin
 * From Rserve java Client & php Client
 * Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
 */
abstract class Parser extends Protocol {
	
	abstract function parse($buf, &$offset);

}
