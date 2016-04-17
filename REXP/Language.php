<?php
/**
* Rserve client for PHP
* Supports Rserve protocol 0103 only (used by Rserve 0.5 and higher)
* $Revision$
* @author Clément TURBELIN
* Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
* This code is inspired from Java client for Rserve (Rserve package v0.6.2) developped by Simon Urbanek(c)
*/

/**
* R Language type vector
*/
class Rserve_REXP_Language extends Rserve_REXP_List {

	public function isLanguage() {
		return TRUE;
	}

	public function getType() {
		return Rserve_Parser::XT_LANG;
	}

}
