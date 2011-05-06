<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleGeneration extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function uniqueMd5Id($seed = null){
		return md5(uniqid(is_null($seed) ? mt_rand() : $seed, true));
	}
	
	
	
	public static function microRandId(){
		return md5(''.microtime().rand());
	}
	
}

?>