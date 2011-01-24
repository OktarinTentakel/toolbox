<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleString extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|GOOGLE-ANALYTICS----------
	
	public function escapeForRegExp($string){
		return str_replace('/', '\/', preg_quote($string));
	}
	
}

?>