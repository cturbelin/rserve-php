<?php

namespace Sentiweb\Rserve;

use Sentiweb\Rserve\Parser\REXP;
use Sentiweb\Rserve\Parser\NativeArray;

/**
 * Simple command handler using a connexion
 * 
 * This class handle basic script evaluation & error handling 
  * 
 * It decorates R command with try() in order to catch error occuring in R
 * 
 * if PARSER_NATIVE is used:
 *  result array will have an entry "try-error" set to true
 *  
 * if PARSER_WRAPPED
 *  result will have an attribute "class" with value "try-error" and an entry try-error
 * 
 * if PARSER_REXP
 *  result class will be an Sentiweb\RServeREXP\Error
 * 
 */

class Evaluator {
	
	const PARSER_NATIVE = 1;
	const PARSER_WRAPPED = 2;
	const PARSER_REXP = 3;
	
	protected $connexion;
	
	protected $parserType;
	
	protected $parser;
	
	public function __construct($connexion, $parser) {
		if( is_array($connexion) ) {
			$this->connexion = new Connection($connexion);
		} else {
			$this->connexion = $connexion;
		}
		$this->parser = $this->createParser($parser);
	}
	
	public function createParser($parser) {
		if( is_integer($parser) ) {
			$this->parserType = $parser;
			if($parser == self::PARSER_NATIVE) {
				return null; // no need to create parser
			}
			if($parser == self::PARSER_WRAPPED) {
				return new NativeArray(['wrapper'=>true]);
			}
			if($parser == self::PARSER_REXP) {
				return new REXP();
			}
		} else {
			return $parser;
		}
	}
	
	public function decorate($command) {
		switch($this->parserType) {
			case self:: PARSER_NATIVE:
			case self:: PARSER_WRAPPED:
				return 'r= try({'.$command.'}, silent=T); if( inherits(r,"try-error")) { r = structure(list("try-error"=1,message=unclass(r)), class="try-error") }; r';
				break;
									
			case self::PARSER_REXP:
				return 'r= try({'.$command.'}, silent=T);';
				break;
		}
		if(!$this->parserType) {
		 throw new Exception('Unhandled parser type');
		}
	}
	
	public function evaluate($command) {
		return $this->connexion->evalString($this->decorate($command), $this->parser);
	}
} 