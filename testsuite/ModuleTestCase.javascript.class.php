<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class JavaScriptTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('JavaScriptTest');
	}
	// ***
	



	//--|TESTS----------	
	
	public function testArrayToJsArrayString(){
		$this->assertEqual(ToolBox::_javascript_()->arrayToJsArrayString(array('one', 2, 3.1)), "['one', 2, 3.1]", 'simple php array must result in flat js-array-string <%s>');
		$this->assertEqual(ToolBox::_javascript_()->arrayToJsArrayString(array(array('one', 2), 3.1, array('four', 'five'))), "[['one', 2], 3.1, ['four', 'five']]", 'recursive php array must result in recursive js-array-string <%s>');
		$this->assertEqual(ToolBox::_javascript_()->arrayToJsArrayString(array('one', new StdClass(), 3.1, null, true)), "['one', 'stdClassObject()', 3.1, null, true]", 'php-array with objects results in js-array-string with string casted objects <%s>');
	}
	
}

?>