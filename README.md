Rserve-php
==========

php5 client for Rserve http://www.rforge.net/Rserve/ (a TCP/IP server for R statistical software)

[![Build Status](https://travis-ci.org/shadiakiki1986/rserve-php.svg?branch=2.0-improvements)](https://travis-ci.org/shadiakiki1986/rserve-php)

Changes from 1.0 version
---
- All classes are declared under Sentiweb\Rserve namespace allowing PSR-4 autoloading
- Parsers are now individualized into classes
- A Parser instance can be directly used as second argument of evalString() to replace default parser (see example)

Tests
-----

You can run tests using phpunit

Several tests need to have a running Rserve server (not handled by this library).
To configure the test to use a server you have to configure the connection using environment vars or by creating a file in tests/config.php and defining constants (a sample file is available in tests/config.php.sample).
Expected vars (in env or as constant in tests/config.php)
- `RSERVE_HOST` : hostname or IP of the Rserve server (e.g. 'localhost' or 127.0.0.1 for local server).
- `RSERVE_PORT` : port number, if value is '0' or 'unix', the HOST is expected to be a unix socket path.
- `RSERVE_USER` : username if the Rserve server is expecting authentication, skip or leave empty if not
- `RSERVE_PASS` : password if the Rserve server is expecting authentication, skip or leave empty if not

`RSERVE_HOST` is required to be  defined (either env or in config.php) to run the connection aware tests, if not these tests will be skipped. 

Credential-free tests
* launch your credential-free Rserve instance
 * Can be done with `docker run -d -p 6311:6311 wnagele/rserve`

* if installed with composer: `composer test`
* otherwise, run with `phpunit` 

Login tests:
This test suite require a credential-protected Rserve instance to be running (not provided by this library)
and the credentials (username and password to be configured as described above)

Installation
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
   $cnx = new \Sentiweb\Rserve\Connection('myserverhost', 6311, ['username'=>username,'password'=>password])
```

Parsers
-----

Results provided by R could be handled using several parsers

 - NativeArray
 	Translates R structure into php simple arrays. It is useful to get simple values from R
 	
 - Wrapped array
   Using NativeArray with parameters `array("wrapper"=>true)` in contructor return object
   with attributes of R objects.
   The result object can be used as an array and provides methods to access R object attributes
   
 - Debug
   Translates R response to structure useful for debugging 	

 - REXP
   Translates R response into REXP classes


Async Mode
-----------

Several functions allow to use connection in async mode

* getSocket() to get the socket an set some options
* setAsync() allow to set the async mode
* getResults($parser) : get and parse the results after a call to evalString() in async mode
