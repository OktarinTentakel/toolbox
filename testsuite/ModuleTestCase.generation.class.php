<?php

//--|INCLUDES----------

require_once 'ModuleTestCase.absclass.php';

require_once 'toolbox/ToolBox.class.php';



//--|CLASS----------

class GenerationTest extends ModuleTestCase {

	// ***
	public function __construct(){
		parent::__construct('GenerationTest');
	}
	// ***
	



	//--|TESTS----------	
	
	public function testUniqueMd5Id(){
		$ids = array();
		$md5AndNoCollision = true;
		
		for( $i = 0; $i < 1000; $i++ ){
			$id = ToolBox::_generation_()->uniqueMd5Id();
			if( in_array($id, $ids) || !preg_match('/^[0-9a-z]{32}$/', $id) ){
				$md5AndNoCollision = false;
				break;
			} else {
				$ids[] = $id;
			}
		}
		
		$this->assertTrue($md5AndNoCollision, '1000 Ids must all be 32byte-md5s and collision-free <%s>');
	}
	
	
	
	public function testMicroRandMd5Id(){
		$ids = array();
		$md5AndNoCollision = true;
	
		for( $i = 0; $i < 1000; $i++ ){
			$id = ToolBox::_generation_()->microRandMd5Id();
			if( in_array($id, $ids) || !preg_match('/^[0-9a-z]{32}$/', $id) ){
				$md5AndNoCollision = false;
				break;
			} else {
				$ids[] = $id;
			}
		}
	
		$this->assertTrue($md5AndNoCollision, '1000 Ids must all be 32byte-md5s and collision-free <%s>');
	}
	
}

?>