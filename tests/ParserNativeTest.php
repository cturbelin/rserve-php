<?php
if ( !defined("PHPUnit_MAIN_METHOD") ) {
    define("PHPUnit_MAIN_METHOD", "ParserNativeTest::main");
}

require_once 'config.php';

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
            // character vector
            array('c("TOTO is TOTO","Ohhhh","String2")', 'string', array("TOTO is TOTO","Ohhhh","String2")),

            // pairlist
            array('list("toto"=1,"titi"=2)',NULL, array('toto'=>1,'titi'=>2)),

            // pairlist
            array('list("toto"=1,"titi"=2, "tutu"="TOTO")', NULL, array('toto'=>1,'titi'=>2,'tutu'=>'TOTO')),

            // data.frame : Caution with data.frame, use stringsAsFactors=F 
            array('data.frame("toto"=c(1,2,3),"titi"=c(2,2,3),"tutu"=c("foo","bar","i need some sleep"), stringsAsFactors =F)', NULL, 
                array('toto'=>array(1,2,3),'titi'=>array(2,2,3),'tutu'=>array('foo','bar','i need some sleep')) ),
                
            array('chisq.test(as.matrix(c(12,58,79,52),ncol=2))[c("statistic","p.value","expected")]',NULL, array('statistic'=>46.8209, 'p.value'=>3.794258e-10,'expected'=>array(50.25,50.25,50.25,50.25)), array('statistic'=>'round|4','p.value'=>'round|16')),
        );
    
    }
      
    
    /**
    * @dataProvider providerSimpleTests
    * @param string $cmd R command
    * @param string $type expected type 
    * @param array $expected expected php structure
    * @param array $filters filters to apply to the R result to fit the tests values, each filter is array(funcname, param1,...), or a string funcname|param1|param2...
    */
    public function testSimpleTypes($cmd, $type, $expected, $filters=NULL) {
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
        if( !is_null($filters) ) {
            foreach($filters as $key=>$filter) {
                if( is_string($filter) ) {
                    $filter = explode('|',$filter);
                }
                $f = array_shift($filter);
                if( !is_callable($f) ) {
                    throw new Exception('Bad filter '.$f.' for '.$key);
                }
                $params = array_merge(array($r[$key]), $filter);
                $r[$key] = call_user_func_array($f, $params);
            }
        }
        $this->assertEquals($r, $expected);
    }

}
