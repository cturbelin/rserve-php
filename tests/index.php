<?php 
/**
* Rserve-php example
*/ 
require_once 'config.php';
require '../Connection.php';

try { 

    echo 'Connecting to Rserve '.RSERVE_HOST;
    $r = new Rserve_Connection(RSERVE_HOST);

    $x = $r->evalString('a=rpois(100,100); b=rpois(100,100)');
    var_dump($x);

    $x = $r->evalString('chisq.test(table(a,b))', Rserve_Connection::PARSER_REXP);

    echo '<style>'.file_get_contents('rexp.css').'</style>';
    echo $x->toHTML();

    $r->close();
} catch(Exception $e) {
    echo $e;
}
?>