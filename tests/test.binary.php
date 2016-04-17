<?php
/**
* Ugly Test for REXP creation
* Work in progress...
*/

require __DIR__ . '/../Connection.php';

require_once __DIR__ . '/config.php';

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

function testBinary($cnx, $values, $type, $options = array(), $msg = '') {
	echo '=================================='.CLI_EOL;
	echo 'Test '.$type.' '.$msg.CLI_EOL;

	$tt  = strtolower($type);

	$rexp = create_REXP($values, $type, $options);

	$bin = Rserve_Parser::createBinary($rexp);
	//var_dump($bin);

	$i = 0;
	echo "Debug REXP".CLI_EOL;
	var_dump(Rserve_Parser::parseDebug($bin, $i));

	$i = 0;
	echo "binary to REXP".CLI_EOL;
	$r2 = Rserve_Parser::parseREXP($bin, $i);
	var_dump($r2);

	$cn2 = get_class($r2);

	$cn = get_class($rexp);
	if( strtolower($cn2) != strtolower($cn)) {
		echo 'Differentes classes'.CLI_EOL;
		return FALSE;
	} else {
		echo 'Class Type ok'.CLI_EOL;
	}
	$r = $cnx->assign('x', $rexp);
	if($r['is_error']) {
		echo $cnx->getErrorMessage($r['error']);
	} else {
		echo "OK";
	}
	echo CLI_EOL;
	echo "Check R object".CLI_EOL;
	$r = $cnx->evalString('x');
	var_dump($r);
}

$cnx = new Rserve_Connection('localhost', 6311, TRUE);

testBinary($cnx, array(1,2,3), 'Integer'  );

testBinary($cnx, array(1.1, 2.2, 3.3), 'Double'  );

testBinary($cnx, array(TRUE, FALSE, TRUE, NULL), 'Logical');

testBinary($cnx, array('toto', 'titi', 'Lorem ispum', NULL), 'String');


//$rexp = create_REXP(array(1,2,3), 'Integer');



