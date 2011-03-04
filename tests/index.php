<?php 
/**
* Rserve-php example
*/ 
require_once 'config.php';
require '../Connection.php';

try { 

    echo 'Connecting to Rserve '.R_SERVE_HOST;
    $r = new Rserve_Connection(R_SERVE_HOST);

    $x = $r->evalString('a=rpois(100,100); b=rpois(100,100)');
    var_dump($x);

    $x = $r->evalString('chisq.test(table(a,b))', FALSE);

    echo '<style>'.file_get_contents('rexp.css').'</style>';
    echo $x->toHTML();

    $r->close();
} catch(Exception $e) {
    echo $e;
}
?>