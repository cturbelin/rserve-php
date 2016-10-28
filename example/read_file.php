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
 
 //create a file on the server
 $r = $cnx->evalString('write.csv(data.frame(1, 1:10000),"testout.csv")');

 // open file for writing
 $handle = fopen("testfromserver.csv","w"); 

 $cnx->openFile($handle,"testout.csv");

 print ("see: testfromserver.csv\n");


 
