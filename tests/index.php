<?php 
/**
* Rserve-php example
*/ 
require_once 'config.php';
require '../Connection.php';
require 'Definition.php';

$test_cases = Rserve_Tests_Definition::$native_tests;

require 'example/head.php';

function mydump($x, $title=NULL) {
    if($title) {
        echo '<h4>'.$title.'</h4>';
    }
    echo '<pre>';
    var_dump($x);
    echo '</pre>';
}

try { 

    echo '<div id="tab_0" class="tab">';
    echo '<p>Connecting to Rserve '.RSERVE_HOST;
    $r = new Rserve_Connection(RSERVE_HOST);
    echo ' OK</p>';
    
    echo '<p>Use the above menu to see results for various results using each kind of parser</p>';
    
    echo '</div>';
    
    echo '<div id="tab_1" class="tab">';
    echo '<h2>Chi2</h2>';
    $x = $r->evalString('a=rpois(100,100); b=rpois(100,100); list(a,b)');

    //mydump($x);
    
    $x = $r->evalString('chisq.test(table(a,b))', Rserve_Connection::PARSER_REXP);

    echo $x->toHTML();
    echo '</div>';
    
    $parsers = array(
        Rserve_Connection::PARSER_NATIVE=>'native ',
        Rserve_Connection::PARSER_NATIVE_WRAPPED=>' wrapped native (RNative)',
        Rserve_Connection::PARSER_DEBUG=>'Parser Debug',
        Rserve_Connection::PARSER_REXP=>'REXP',
    );
    
    $i = 1;
    foreach($parsers as $parser=>$title) {
        ++$i;
        echo '<div id="tab_'.$i.'" class="tab">';
        echo '<h2>Test Cases / '.$title.'</h2>';
        foreach($test_cases as $test) {
            $cmd = $test[0];
            echo '<div class="rcmd">&gt; '.$cmd.'</div>';
            $x = $r->evalString($cmd, $parser);
            echo '<div class="vardump">';
            mydump($x);
            echo '</div>';
        }
        echo '</div>';
    }
    
    ++$i;
    echo '<div id="tab_'.$i.'" class="tab">';
    echo '<h2>Dataframe</h2>';
    $cmd = 'data.frame(sexe=c("F","M","F","M"), age=c(10,22,23,44), weight=c(20,55,60,67))';
    $x = $r->evalString($cmd, Rserve_Connection::PARSER_REXP); 
    
    mydump($x, 'REXP object');
    
    mydump($x->getClass(),'getClass()');
    
    mydump($x->getRowNames(), 'getRowNames()');
    
    mydump($x->getNames(), 'getNames()');
    
    mydump($x->nrow(), 'nrow()');
    
    mydump($x->ncol(), 'ncol()');
    
    echo '</div>';
    
    
    ++$i;
    echo '<div id="tab_'.$i.'" class="tab">';
    echo '<h2>Complex</h2>';
    $cmd = 'x = 1:10 + rnorm(10)*1i';
    $x = $r->evalString($cmd, Rserve_Connection::PARSER_REXP); 
    
    mydump($x, 'REXP object');
    
    echo $x->toHTML();
    
    $x = $r->evalString($cmd, Rserve_Connection::PARSER_NATIVE); 
    
    mydump($x,'Native');
    
    echo '</div>';
    
    
    $r->close();
} catch(Exception $e) {
    echo $e;
}

require 'example/foot.php';

?>
