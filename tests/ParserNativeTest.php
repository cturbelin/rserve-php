<?php
if ( !defined("PHPUnit_MAIN_METHOD") ) {
    define("PHPUnit_MAIN_METHOD", "Periode_WeekTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once dirname(__FILE__).'/../Connection.php';

class ParserNativeTest extends PHPUnit_Framework_TestCase {

    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";
        $suite  = new PHPUnit_Framework_TestSuite("Periode_WeekTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        $this->rserve = new Rserve_Connection('134.157.220.27');
    }
    
    
    function providerSimpleTests() {
        return array(
            array('TRUE', 'bool', TRUE),
            array('c(T,F,T,F,T,F,F)', NULL, array(TRUE,FALSE,TRUE,FALSE,TRUE,FALSE,FALSE)),
            array('as.integer(12345)', 'int', 12345),
            array('as.integer(c(34, 45, 34, 93, 604, 376, 2, 233456))', NULL, array(34,45,34,93,604,376,2, 233456))
        );
    
    }
      
    
    /**
    * @dataProvider providerSimpleTests
    */
    public function testSimpleTypes($cmd, $type, $expected) {
        $r = $this->rserve->evalString($cmd);
        if( !is_null($type) ) {
            $this->assertType($type, $r);
        }
        $this->assertEquals($r, $expected);
    }

}
