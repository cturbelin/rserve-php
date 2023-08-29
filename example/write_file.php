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
 
 //upload a file to the server

 $handle = fopen("exampledata.csv","r"); 

 $cnx->createFile($handle,"exdata.csv");

 $r = $cnx->evalString('list.files(".")');

 var_dump($r); 

 $r = $cnx->evalString('read.csv("exdata.csv")');

 var_dump($r); 

 //test with binary files

 print ("Binary file:\n");

 $handle = fopen("exampledata.sav","r");

 $cnx->createFile($handle,"exdata.sav");

 $r = $cnx->evalString('library(foreign); read.spss("exdata.sav")');

 var_dump($r); 
