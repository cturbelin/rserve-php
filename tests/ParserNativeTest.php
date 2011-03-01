<?php
if ( !defined("PHPUnit_MAIN_METHOD") ) {
    define("PHPUnit_MAIN_METHOD", "ParserNativeTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once dirname(__FILE__).'/../Connection.php';
require_once dirname(__FILE__).'/config.php';

class ParserNativeTest extends PHPUnit_Framework_TestCase {

    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";
        $suite  = new PHPUnit_Framework_TestSuite("ParserNativeTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        $this->rserve = new Rserve_Connection(RSERVE_HOST);
    }
    
    /**
    * Provider for test cases
    */
    function providerSimpleTests() {
        return array(
            // logical value
            array('TRUE', 'bool', TRUE),
            // logical vector
            array('c(T,F,T,F,T,F,F)', 'bool', array(TRUE,FALSE,TRUE,FALSE,TRUE,FALSE,FALSE)),
            // integer value
            array('as.integer(12345)', 'int', 12345),
            // integer vector
            array('as.integer(c(34, 45, 34, 93, 604, 376, 2, 233456))', 'int', array(34,45,34,93,604,376,2, 233456)),
            // numeric
            array('c(34.2, 45.5, 987.2, 22.1, 87.0, 345.0, 1E-6, 1E38)', 'float', array(34.2, 45.5, 987.2, 22.1, 87.0, 345.0, 1E-6, 1E38)),
            // character
            array('"TOTO is TOTO"', 'string', 'TOTO is TOTO'),
            // pairlist
            array('list("toto"=1,"titi"=2)',NULL, array('toto'=>1,'titi'=>2)),
            // data.frame
            array('data.frame("toto"=c(1,2,3),"titi"=c(2,2,3))',NULL, array('toto'=>array(1,2,3),'titi'=>array(2,2,3)) ),

        );
    
    }
      
    
    /**
    * @dataProvider providerSimpleTests
    */
    public function testSimpleTypes($cmd, $type, $expected) {
        $r = $this->rserve->evalString($cmd);
        if( is_array($expected) ) {
            $this->assertType('array',$r);
            if( is_array($r) AND !is_null($type) ) {
                foreach($r as $x) {
                    $this->assertType($type,$x);
                }
            }
        } else {
            if( !is_null($type) ) {
                $this->assertType($type, $r);
            }
        }
        $this->assertEquals($r, $expected);
    }

}
