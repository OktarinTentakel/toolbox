<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class ObjectTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('ObjectTest');
	}
	// ***



	//--|TESTS----------
	
	public function testToAssocArray(){
		$one = new StdClass();
		$one->two = 'three';
		$one->four = array(true, 3.1, 1, null);
		$two = array($one, $one);
		
		$oneArray = array('two' => 'three', 'four' => array(true, 3.1, 1, null));
		
		$this->assertEqual(ToolBox::_object_()->toAssocArray($one), $oneArray, 'an object is transferred into an associative array containing available members as keys/values <%s>');
		$this->assertEqual(ToolBox::_object_()->toAssocArray($two), array($oneArray, $oneArray), 'an array, containing objects, is transferred into a normal array containing associative array transformed from objects <%s>');
		$this->assertEqual(ToolBox::_object_()->toAssocArray(array(null, 1)), array(null, 1), 'an array of ordinal values stays that way <%s>');
		$this->assertEqual(ToolBox::_object_()->toAssocArray(3.2), 3.2, 'an ordinal value stays that way <%s>');
	}
	
}

?>