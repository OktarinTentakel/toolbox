<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class VariableTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('VariableTest');
	}
	// ***



	//--|TESTS----------
	
	public function testApplyRuleToValues(){
		$closure = function($val){
			return $val == true;
		};
		
		$this->assertTrue(ToolBox::_variable_()->applyRuleToValues($closure, array(true, true, true)), 'A simple closure validates several values correctly <%s>');
		$this->assertFalse(ToolBox::_variable_()->applyRuleToValues($closure, array(true, false, 0.5)), 'A simple closure devalidates several values correctly <%s>');
	}
	
	
	
	public function testIsNull(){
		$this->assertTrue(ToolBox::_variable_()->isNull(null, null, null), 'All null validates <%s>');
		$this->assertFalse(ToolBox::_variable_()->isNull(null, 0, null), 'Mixed values don\'t validate <%s>');
	}
	
	
	
	public function testIsNotNull(){
		$this->assertTrue(ToolBox::_variable_()->isNotNull(false, 0.5, 'test'), 'All not null validates <%s>');
		$this->assertFalse(ToolBox::_variable_()->isNotNull(false, 0.5, null), 'Mixed values don\'t validate <%s>');
	}
	
	
	
	public function testIsEmpty(){
		$this->assertTrue(ToolBox::_variable_()->isEmpty(false, 0, null), 'All empty validates <%s>');
		$this->assertFalse(ToolBox::_variable_()->isEmpty('fluffy', 0, null), 'Mixed values don\'t validate <%s>');
	}
	
	
	
	public function testIsNotEmpty(){
		$this->assertTrue(ToolBox::_variable_()->isNotEmpty(true, 0.1, new StdClass()), 'All empty validates <%s>');
		$this->assertFalse(ToolBox::_variable_()->isNotEmpty(false, 0.1, new StdClass()), 'Mixed values don\'t validate <%s>');
	}
	
}

?>