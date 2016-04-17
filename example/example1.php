<?php
 require __DIR__."/../src/autoload.php";
 
 /**
  * To run example create config.php
  * defining following constants 
  * 
  * define('RSERVE_HOST', 'localhost'); // Host to use 
  * 
  */ 
 require __DIR__.'/config.php';
 
 use Sentiweb\Rserve\Connection;
 use Sentiweb\Rserve\Parser\NativeArray;
 
 $cnx = new Connection(RSERVE_HOST);
 
 // Run a Chi square test and return the result
 $r = $cnx->evalString('chisq.test(c(12,223,22,10))');
 

 var_dump($r);
 
// Do the same ChiSQ but using ArrayWrapper to get attributes with result
 $parser = new NativeArray(array('wrapper'=>true));
 $r = $cnx->evalString('chisq.test(c(12,223,22,10))', $parser);
 
 var_dump($r);
 
 