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
 use Sentiweb\Rserve\Exception as Rserve_Exception;
use Sentiweb\Rserve\Parser\REXP;
use Sentiweb\Rserve\Parser\Sentiweb\Rserve\Parser;
 
 $cnx = new Connection(RSERVE_HOST);
 
 // Using NativeArray
 
 // Run an error
 try {
 	$command = 'seq(1,10, by=NA)';
 	$command = 'r= try({'.$command.'}, silent=T); if( inherits(r,"try-error")) { r = structure(list("try-error"=TRUE, message=unclass(r))) }; r';
 	$r = $cnx->evalString($command);
 	//var_dump($r);
 } catch(Rserve_Exception $e) {
 	var_dump($e);
 }

 echo "NativeArray\n";
 $parser = new NativeArray(array('wrapper'=>true));
 try {
 	$command = 'seq(1,10, by=NA)';
 	$command = 'r= try({'.$command.'}, silent=T); if( inherits(r,"try-error")) { r = structure(list(message=unclass(r)), class="try-error") }; r';
 	$r = $cnx->evalString($command, $parser);
 	
 	var_dump($r);
 } catch(Rserve_Exception $e) {
 	var_dump($e);
 }
 
 echo "REXP\n";
 $parser = new REXP();
 try {
 	$command = 'seq(1,10, by=NA)';
 	$command = 'try({'.$command.'}, silent=T)';
 	$r = $cnx->evalString($command, $parser);
 	
 	$class = $r->getAttribute('class');
 	if($class) {
 		$class = $class->getValues();
 	}
 	
 	//var_dump($class);
 	
 	//var_dump($r);
 } catch(Rserve_Exception $e) {
 	var_dump($e);
 }
   
