<?php

namespace Sentiweb\Rserve\Tests;

/**
* Tests Definition
*/
class Definition {

	const TYPE_BOOL = 'bool';
	const TYPE_INT = 'int';
	const TYPE_FLOAT = 'float';
	const TYPE_STRING = 'string';

	/**
	 * array of tests cases
	 * array( Rcommand, expected_type , result, filters)
	 * expected_type : if a type is expected for the results
	 * results = expected results for the native parser
	 * filters = array of filters (filter the parsed value before to test it with the expected result)
	 */
	public static $native_tests = [
     // logical value
     ['TRUE', self::TYPE_BOOL, true],
     // logical vector
     ['c(T,F,T,F,T,F,F)', self::TYPE_BOOL, [true, false, true, false, true, false, false]],
     // integer value
     ['as.integer(12345)', self::TYPE_INT, 12345],
     // integer vector
     ['as.integer(c(34, 45, 34, 93, 604, 376, 2, 233456))', self::TYPE_INT, [34, 45, 34, 93, 604, 376, 2, 233456]],
     // numeric
     ['c(34.2, 45.5, 987.2, 22.1, 87.0, 345.0, 1E-6, 1E38)', self::TYPE_FLOAT, [34.2, 45.5, 987.2, 22.1, 87.0, 345.0, 1E-6, 1E38]],
     // character
     ['"TOTO is TOTO"', self::TYPE_STRING, 'TOTO is TOTO'],
     // character vector
     ['c("TOTO is TOTO","Ohhhh","String2")', self::TYPE_STRING, ["TOTO is TOTO", "Ohhhh", "String2"]],
     // pairlist
     ['list("toto"=1,"titi"=2)', null, ['toto'=>1, 'titi'=>2]],
     // pairlist
     ['list("toto"=1,"titi"=2, "tutu"="TOTO")', null, ['toto'=>1, 'titi'=>2, 'tutu'=>'TOTO']],
     // factors
     ['factor(c("toto","titi","toto","tutu"))', null, ["toto", "titi", "toto", "tutu"]],
     // data.frame : Caution with data.frame, use stringsAsFactors=F
     ['data.frame("toto"=c(1,2,3),"titi"=c(2,2,3),"tutu"=c("foo","bar","i need some sleep"), stringsAsFactors =F)', null, ['toto'=>[1, 2, 3], 'titi'=>[2, 2, 3], 'tutu'=>['foo', 'bar', 'i need some sleep']]],
     ['chisq.test(as.matrix(c(12,58,79,52),ncol=2))[c("statistic","p.value","expected")]', null, ['statistic'=>46.8209, 'p.value'=>3.794258e-10, 'expected'=>[50.25, 50.25, 50.25, 50.25]], ['statistic'=>'round|4', 'p.value'=>'round|16']],
 ];

}
