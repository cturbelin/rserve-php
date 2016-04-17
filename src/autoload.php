<?php
// Basic autoloader in case of use outside from composer

require_once(__DIR__.'/lib/helpers.php');

spl_autoload_register(function($classname) {
	if(substr($classname, 0, 15)=== "Sentiweb\\Rserve") {
		$fn = substr($classname, 16);
		$fn = str_replace("\\", "/", $fn).'.php';
		require_once __DIR__.'/'.$fn;
		return true;	
	}
	return false;
});
