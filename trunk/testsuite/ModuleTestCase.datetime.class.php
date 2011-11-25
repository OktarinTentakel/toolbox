<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class DateTimeTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('DateTimeTest');
	}
	// ***



	//--|TESTS----------
	
	public function testMiscToIsoDate(){
		//$this->assertEqual(ToolBox::_array_()->concat($one, $two, $three, $four, $five, $six), array('one', 'two', 'three', 'four', 'five six', $four, 'five', array('six', 'seven'), 'eight'), 'nested array are not flattened <%s>');
	}
	
}

?>