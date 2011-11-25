<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class ArrayTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('ArrayTest');
	}
	// ***



	//--|TESTS----------
	
	public function testConcat(){
		$one = 'one';
		$two = array('two', 'three', 'four');
		$three = 'five six';
		$four = new StdClass();
		$five = 'five';
		$four->five = $five;
		$six = array(array('six', 'seven'), 'eight');
		
		$this->assertEqual(ToolBox::_array_()->concat($one, $two, $three), array('one', 'two', 'three', 'four', 'five six'), 'mixed parameters result in flattened array <%s>');
		$this->assertEqual(ToolBox::_array_()->concat($one, $two, $three, $four, $five), array('one', 'two', 'three', 'four', 'five six', $four, 'five'), 'mixed parameters keep their types and are not cast to strings <%s>');
		$this->assertEqual(ToolBox::_array_()->concat($one, $two, $three, $four, $five, $six), array('one', 'two', 'three', 'four', 'five six', $four, 'five', array('six', 'seven'), 'eight'), 'nested array are not flattened <%s>');
	}
	
	
	
	public function testAssocToObject(){
		$one = array(
			'one' => 'two',
			'three' => array('four', 'five'),
			'six' => (object)array('seven' => 'eight')
		);
		$oneObj = new StdClass();
		$oneObj->one = 'two';
		$oneObj->three = array('four', 'five');
		$oneObj->six = (object)array('seven' => 'eight');
		
		$two = array(
			0 => 'nine',
			66 => 'ten',
			'eleven' => 'twelve'
		);
		$twoObj = new StdClass();
		$twoObj->eleven = 'twelve';
		
		$this->assertEqual(ToolBox::_array_()->assocToObject($one), $oneObj, 'an associative array is transferred into a StdClass-object, just like casting to object would do <%s>');
		$this->assertEqual(ToolBox::_array_()->assocToObject($one), $oneObj, 'an associative array containing numeric indices is transferred into a StdClass-object containing only string-key-based properties, unlike casting to object would do <%s>');
		$this->assertEqual(ToolBox::_array_()->assocToObject(array('one', 'two', 'three')), new StdClass(), 'a standard array, only containing numeric indices results in an empty object <%s>');
	}
	
}

?>