<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class ValidationTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('ValidationTest');
	}
	// ***



	//--|TESTS----------
	
	public function testIsInteger(){
		$this->assertTrue(
			ToolBox::_validation_()->isInteger(5)
			&& ToolBox::_validation_()->isInteger(-1234)
			&& ToolBox::_validation_()->isInteger(32768)
			&& ToolBox::_validation_()->isInteger('42'),
			'Standard ints are recognized correctly <%s>'
		);
		
		$this->assertTrue(
			!ToolBox::_validation_()->isInteger(1.4)
			&& !ToolBox::_validation_()->isInteger(-1234, false)
			&& !ToolBox::_validation_()->isInteger(0800),
			'Non-ints, or ints out of signed-range, are not recognized <%s>'
		);
	}
	
	
	
	public function testIsBoolean(){
		$this->assertTrue(
			ToolBox::_validation_()->isBoolean(true)
			&& ToolBox::_validation_()->isBoolean(false)
			&& ToolBox::_validation_()->isBoolean('true', true)
			&& ToolBox::_validation_()->isBoolean('false', true),
			'Boolean values are recognized correctly <%s>'
		);
		
		$this->assertTrue(
			!ToolBox::_validation_()->isBoolean(0)
			&& !ToolBox::_validation_()->isBoolean(1)
			&& !ToolBox::_validation_()->isBoolean('yes')
			&& !ToolBox::_validation_()->isBoolean('no'),
			'Pseudo-boolean are not recognized <%s>'
		);
	}
	
	
	
	public function testIsUrl(){
		$this->assertTrue(
			ToolBox::_validation_()->isUrl('http://www.fluffykitten.com#kittycat')
			&& ToolBox::_validation_()->isUrl('https://fluffy.kitten.com:8080/index.php?fluffy=kitten')
			&& ToolBox::_validation_()->isUrl('ftp://download.thekitten.de:20')
			&& ToolBox::_validation_()->isUrl('www.fluffykitten.com', false),
			'Correct URLs are recognized correctly <%s>'
		);
		
		$this->assertTrue(
			!ToolBox::_validation_()->isUrl('http://localhost:80')
			&& !ToolBox::_validation_()->isUrl('spdy://www.fluffykitten.com')
			&& !ToolBox::_validation_()->isUrl('http://fluffy.com/?kitte=cat&cat=kitte?fluffy=kitten')
			&& !ToolBox::_validation_()->isUrl('the.website:123456', false),
			'Malformed and too short URLs (localhost e.g.) are not recognized <%s>'
		);
	}
	
	
	
	public function testIsEmail(){
		$this->assertTrue(
			ToolBox::_validation_()->isEmail('fluffy@kitten.com')
			&& ToolBox::_validation_()->isEmail('fluffy-kitten.kittentown@go.de')
			&& ToolBox::_validation_()->isEmail('fu@ba.org'),
			'Correct Email-addresses are recognized correctly <%s>'
		);
		
		$this->assertTrue(
			!ToolBox::_validation_()->isEmail('fluffy@kitten@aol.com')
			&& !ToolBox::_validation_()->isEmail('fluffy@kitten.t')
			&& !ToolBox::_validation_()->isEmail('4lu$$ü.k177n@cat.cat')
			&& !ToolBox::_validation_()->isEmail('@kitten.com')
			&& !ToolBox::_validation_()->isEmail('f@k.com'),
			'Malformed Email-addresses are not recognized <%s>'
		);
	}
	
}

?>