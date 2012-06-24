<?php
/**
* Ugly Test for REXP creation
* Work in progress...
*/

require '../Connection.php';
require '../Binary.php';

require_once 'config.php';

define('CLI_EOL',"\n");


function create_REXP($values, $type, $options=array()) {
	$cn = 'Rserve_REXP_'.$type;
	$r = new $cn();
	if(is_subclass_of($r, 'Rserve_REXP_Vector')) {
		if( is_subclass_of($r,'Rserve_REXP_List') AND @$options['named']) {
			$r->setValues($values, TRUE);
		} else {
			$r->setValues($values);
		}
	} else {
		$r->setValue($values);
	}
	return $r;
}

function testBinary($values, $type, $options = array(), $msg = '') {
	echo 'Test '.$type.' '.$msg.CLI_EOL;
	
	$tt  = strtolower($type);
	
	$r = create_REXP($values, $type, $options);
	
	$bin = Rserve_Parser::createBinary($r);
	//var_dump($bin);
	
	$i = 0;
	var_dump(Rserve_Parser::parseDebug($bin, $i));
	
	$i = 0;
	$r2 = Rserve_Parser::parseREXP($bin, $i);
	var_dump($r2);
	
	$cn2 = get_class($r2);
	
	$cn = get_class($r);
	if( strtolower($cn2) != strtolower($cn)) {
		echo 'Differentes classes';
		return FALSE;
	} else {
		echo 'Class Type ok';
	}
}

testBinary(array(1,2,3), 'Integer'  );

testBinary(array(1.1, 2.2, 3.3), 'Double'  );

testBinary( array(TRUE, FALSE, TRUE, NULL), 'Logical');


$rexp = create_REXP(array(1,2,3), 'Integer');

$cnx = new Rserve_Binary('localhost', 6311, TRUE);

$r = $cnx->assign('x', $rexp);

var_dump($r);

echo $cnx->getErrorMessage($r['error']);
