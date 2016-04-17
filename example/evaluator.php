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
 use Sentiweb\Rserve\Evaluator;
 use Sentiweb\Rserve\Parser\NativeArray;
 
 $cnx = new Connection(RSERVE_HOST);

 $eval = new Evaluator($cnx, Evaluator::PARSER_WRAPPED);
 // Run an bad command
 $r = $eval->evaluate('seq(1,10, by=NA)');
 var_dump($r);
 // Should have an class attribute set to "try-error"
 
 
 // Create evaluator with REXP parser
 $eval = new Evaluator($cnx, Evaluator::PARSER_REXP);
 $r = $eval->evaluate('seq(1,10, by=NA)');
 var_dump($r);
 // Return an Error instance