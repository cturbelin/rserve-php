Rserve-php
==========

php5 client for Rserve http://www.rforge.net/Rserve/ (a TCP/IP server for R statistical software)

[![Build Status](https://travis-ci.org/shadiakiki1986/rserve-php.svg?branch=2.0-improvements)](https://travis-ci.org/shadiakiki1986/rserve-php)

Changes from 1.0 version
---
- All classes are declared under Sentiweb\Rserve namespace allowin PSR-4 autoloading
- Parser are now individualized into classes
- Parser instance should be directly used as second argument of evalString() to use another parser than default (see example)

Tests
-----

You can run tests using phpunit


Credential-free tests
* launch your credential-free Rserve instance
 * Can be done with `docker run -d -p 6311:6311 wnagele/rserve`

* Create a file config.php in the "tests" directory (copy config.php.sample)
* define the constant `RSERVE_HOST` with the address of your Rserve server
 * port used is the default 6311
 * custom port implemented but not supported in tests yet
* if installed with composer: `composer test`
* otherwise, run with `phpunit` 
 * phpunit tests/ParserNativeTest.php
 * phpunit tests\SessionTest.php
 * phpunit tests\REXPTest.php


Login tests:
* launch your credential-protected Rserve instance
* define the constants `RSERVE_PORT, RSERVE_USER, RSERVE_PASS` in config.php (along with `RSERVE_HOST`)
* run test
 * `phpunit tests\LoginTest.php`


Usage
---------

Using without composer :
 include src/autoload.php in your project

Using with composer:
* run `composer require cturbelin/rserve-php:2.0.x-dev`
* add `require __DIR__.'/../vendor/autoload.php';` to your project

Some usage example are provided in [example](example) directory


Using Login Authorization
-------------------------
Usage is the same as the vanilla usage, except for the constructor
```php
   $cnx = new \Sentiweb\Rserve\Connection('myserverhost', serverport, array('username'=>username,'password'=>password))
```

Parsers
-----

Results provided by R could be handled using several parsers

 - NativeArray
 	Translate R structure into php simple arrays. It is useful to get simple values from R
 	
 - Wrapped array
   Using NativeArray with parameters `array("wrapper"=>true)` in contructor return object
   with attributes of R objects.
   The result object is used as an array and also provides methods to access attributes()
   
 - Debug
   Translate R response to structure useful for debugging 	

 - REXP
   Translate R response into REXP classes


Async Mode
-----------

Several functions allow to use connection in async mode

* getSocket() to get the socket an set some options
* setAsync() allow to set the async mode
* getResults($parser) : get and parse the results after a call to evalString() in async mode


Contacts
--------
* Clément Turbelin, clement.turbelin@gmail.com
* http://www.sentiweb.fr
* Université Pierre et Marie Curie - Paris 6, France
