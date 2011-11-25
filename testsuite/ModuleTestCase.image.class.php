<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class ImageTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('ImageTest');
	}
	// ***



	//--|TESTS----------

	public function testDominantColors(){
		$dominantColors = array_keys(ToolBox::_image_()->getDominantColors(dirname(__FILE__).'/../media/download.png'));
		$this->assertEqual($dominantColors[0], '404040', 'finds first color <%s>');
		$this->assertEqual($dominantColors[1], 'f0f0f0', 'finds second color found <%s>');
		$this->assertFalse(in_array('000000', $dominantColors), 'doesn\'t find missing color <%s>');
		$this->assertEqual(count($dominantColors), 2, 'finds exactly two colors <%s>');
		
		$this->expectException('Exception', 'missing file causes exception <%s>');
		$dominantColors = ToolBox::_image_()->getDominantColors(dirname(__FILE__).'/../media/notthere.jpg');
	}
	
	
	
	public function testHexColorToDecArray(){
		$this->assertEqual(ToolBox::_image_()->hexColorToDecArray('abcdef'), array(171, 205, 239), 'RGB representations match (without hash) <%s>');
		$this->assertEqual(ToolBox::_image_()->hexColorToDecArray('#abcdef'), array(171, 205, 239), 'RGB representations match (with hash <%s>)');
		$this->assertEqual(ToolBox::_image_()->hexColorToDecArray('abcdef', 0.5), array(171, 205, 239, 0.5), 'added alpha results in RGBA-Array <%s>');
		$this->assertEqual(ToolBox::_image_()->hexColorToDecArray('abcdef', 1.1), array(171, 205, 239, 1.0), 'added too high alpha results in RGBA-Array with 1.0 alpha <%s>');
		$this->assertEqual(ToolBox::_image_()->hexColorToDecArray('abcdef', -0.31), array(171, 205, 239, 0.0), 'added too low alpha results in RGBA-Array with 0.0 alpha <%s>');
		$this->assertEqual(ToolBox::_image_()->hexColorToDecArray('abcdef', 0.5, true), 'rgba(171, 205, 239, 0.5)', 'added alpha and set last parameter returns rgba-string <%s>');
		
		$this->expectExceptionStack(array(
			'wrong hex format without hash (abcde) causes exception <%s>' => function(){ ToolBox::_image_()->hexColorToDecArray('abcde'); },
			'wrong hex format without hash (gabcde) causes exception <%s>' => function(){ ToolBox::_image_()->hexColorToDecArray('gabcde'); },
			'wrong hex format with hash (#123) causes exception <%s>' => function(){ ToolBox::_image_()->hexColorToDecArray('#123'); },
			'wrong hex format with hash (#12345h) causes exception <%s>' => function(){ ToolBox::_image_()->hexColorToDecArray('#12345h'); }
		));
	}
	
}

?>