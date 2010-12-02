<?php

require '../Connection.php';

function testBinary($values, $type, $options = array(), $msg = '') {
	echo 'Test '.$type.' '.$msg.'<br/>';
	$cn = 'Rserve_REXP_'.$type;
	$r = new $cn();
	
	$tt  = strtolower($type);
	
	if(is_subclass_of($r, 'Rserve_REXP_Vector')) {
		if( is_subclass_of($r,'Rserve_REXP_List') AND @$options['named']) {
			$r->setValues($values, TRUE);			
		} else {
			$r->setValues($values);
		}
	} else {
		$r->setValue($values);
	}
	$bin = Rserve_Parser::createBinary($r);
	//var_dump($bin);
	var_dump(Rserve_Parser::parseDebug($bin, 0));
	$r2 = Rserve_Parser::parseREXP($bin, 0);
	var_dump($r2);
	$cn2 = get_class($r2);
	if( strtolower($cn2) != strtolower($cn)) {
		echo 'Differentes classes';
		return FALSE;
	} else {
		echo 'Class Type ok';
	}
}

testBinary(array(1,2,3), 'Integer'  );

testBinary(array(1.1,2.2,3.3), 'Double'  );

testBinary( array(TRUE, FALSE, TRUE, NULL), 'Logical');