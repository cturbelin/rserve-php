<?php 

require '../Connection.php';

$r = new Rserve_Connection();

$r->evalString('a=rpois(100,100); b=rpois(100,100)');

$x = $r->evalString('chisq.test(table(a,b))', FALSE);

echo '<style>'.file_get_contents('rexp.css').'</style>';
echo $x->toHTML();

$r->close();
?>