<?php

//--|INCLUDES----------

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class ImageTest extends UnitTestCase {

	// ***
	public function __construct(){
		parent::__construct('Image Test');
	}
	// ***



	//--|TESTS----------

	public function testDominantColors(){
		$dominantColors = array_keys(ToolBox::_image_()->getDominantColors(dirname(__FILE__).'/../media/download.png'));
		$this->assertEqual($dominantColors[0], '404040');
		$this->assertEqual($dominantColors[1], 'f0f0f0');
	}
}

?>