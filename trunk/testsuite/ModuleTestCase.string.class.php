<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class StringTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('StringTest');
	}
	// ***
	



	//--|TESTS----------	
	
	public function testEscapeForRegExp(){
		$this->assertEqual(ToolBox::_string_()->escapeForRegExp('/^\w\s\d\W\S\D[^].+*$/'), '\/\^\\\\w\\\\s\\\\d\\\\W\\\\S\\\\D\[\^\]\.\+\*\$\/', 'a string containing regex-characters gets escaped for raw regexp-usage <%s>');
		$this->assertEqual(ToolBox::_string_()->escapeForRegExp('abcdefghijklmnopqrstuvwxyz'), 'abcdefghijklmnopqrstuvwxyz', 'a string not containing regex-characters stays like it is <%s>');
	}
	
	
	
	public function testUtf8Environment(){
		$this->assertFalse(ToolBox::_string_()->utf8EnvironmentSet(), 'initially there is no utf-8-environment set <%s>');
		ToolBox::_string_()->setUtf8Environment();
		$this->assertTrue(ToolBox::_string_()->utf8EnvironmentSet(), 'after the manual setting of an utf-8-environment, the setting is correctly discovered <%s>');
	}
	
	
	
	public function testDecodeUnicodeCodes(){
		$this->assertEqual(ToolBox::_string_()->decodeUnicodeCodes('abc\u00e4\u00f6\u00fcdef'), 'abcäöüdef', 'an iso-string containing unicode-codes results in an unicode-string with the corresponding characters <%s>');
		$this->assertEqual(ToolBox::_string_()->decodeUnicodeCodes('Fluffy kittens!'), 'Fluffy kittens!', 'a string not containing any unicodes stays untouched <%s>');
	}
	
	
	
	public function testMbStrRev(){
		$this->assertEqual(ToolBox::_string_()->auto_strrev('aäöüÄÖÜßz'), 'zßÜÖÄüöäa', 'an unicode-string gets correctly reversed <%s>');
		$this->assertEqual(ToolBox::_string_()->auto_strrev('fluffy kittens...'), '...snettik yffulf', 'an iso-string also gets correctly reversed <%s>');
	}
	
	
	
	public function testParseDbDistinctInformation(){
		$parseTarget = new StdClass();
		$parseTarget->special_display_name = 'fluffykitten';
		$parseTarget->tooltip_text = 'lorem ipsum dolor sit amet';
		$emptyParseTarget = new StdClass();
		
		$this->assertEqual(ToolBox::_string_()->parseDbDistinctInformation('special_display_name-fluffykitten..:NL:..tooltip_text-lorem ipsum dolor sit amet', '-'), $parseTarget, 'a properly formatted string results in a corresponding object <%s>');
		$this->assertEqual(ToolBox::_string_()->parseDbDistinctInformation('lorem ipsum dolor sit amet'), $emptyParseTarget, 'a string without formatting results in an empty object <%s>');
	}
	
	
	
	public function testTruncate(){
		$testString = 'The fluffy kitten jumps on the sofa, while saying "meow!".';
		
		$this->assertEqual(ToolBox::_string_()->truncate($testString, 20, '___'), 'The fluffy kitten ju___', 'the test string gets correctly truncated <%s>');
		$this->assertEqual(ToolBox::_string_()->truncate($testString, 52), 'The fluffy kitten jumps on the sofa, while saying "m...', 'the test string gets correctly truncated to a different length <%s>');
		$this->assertEqual(ToolBox::_string_()->truncate($testString), 'The fluffy kitten jumps on the sofa, while saying "meow!".', 'a truncation with more characters than the string has, results in no modification <%s>');
	}
	
	
	
	public function testStartsWith(){
		$testString = 'The fluffy kitten jumps on the sofa, while saying "meow!".';
		
		$this->assertTrue(ToolBox::_string_()->startsWith($testString, 'The fluffy k'), 'The start gets identified correctly <%s>');
		$this->assertFalse(ToolBox::_string_()->startsWith($testString, 'The fuffy'), 'A wrong start returns nothing <%s>');
	}
	
	
	
	public function testEndsWith(){
		$testString = 'The fluffy kitten jumps on the sofa, while saying "meow!".';
		
		$this->assertTrue(ToolBox::_string_()->endsWith($testString, ' "meow!".'), 'The end gets identified correctly <%s>');
		$this->assertFalse(ToolBox::_string_()->endsWith($testString, '"weow!".'), 'A wrong end returns nothing <%s>');
	}
	
	
	
	public function testSplitByCaps(){
		$testString1 = 'asTheFluffyKittenJumpsTheSofa';
		$testString2 = 'asTheFluffyÄKittenÖJumpsÜTheSofa';
		
		$this->assertEqual(ToolBox::_string_()->splitByCaps($testString1, ';'), 'as;The;Fluffy;Kitten;Jumps;The;Sofa', 'A camelcased string gets split correctly at A-Z <%s>');
		$this->assertEqual(ToolBox::_string_()->splitByCaps($testString2), 'as The FluffyÄ KittenÖ JumpsÜ The Sofa', 'A camelcased string containing capital non-iso characters only gets split at the iso capitals <%s>');
	}
	
}

?>