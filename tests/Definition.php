<?php
/**
* Tests Definition
*/
class Rserve_Tests_Definition {

	/**
	 * array of tests cases
	 * array( Rcommand, expected_type , result, filters)
	 * expected_type : if a type is expected for the results
	 * results = expected results for the native parser
	 * filters = array of filters (filter the parsed value before to test it with the expected result)
	 */
	public static $native_tests = array(
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

		// factors
		array('factor(c("toto","titi","toto","tutu"))',NULL, array("toto","titi","toto","tutu")),

		// data.frame : Caution with data.frame, use stringsAsFactors=F
		array('data.frame("toto"=c(1,2,3),"titi"=c(2,2,3),"tutu"=c("foo","bar","i need some sleep"), stringsAsFactors =F)', NULL,
			array('toto'=>array(1,2,3),'titi'=>array(2,2,3),'tutu'=>array('foo','bar','i need some sleep')) ),

		array('chisq.test(as.matrix(c(12,58,79,52),ncol=2))[c("statistic","p.value","expected")]',NULL, array('statistic'=>46.8209, 'p.value'=>3.794258e-10,'expected'=>array(50.25,50.25,50.25,50.25)), array('statistic'=>'round|4','p.value'=>'round|16')),
	);

}
