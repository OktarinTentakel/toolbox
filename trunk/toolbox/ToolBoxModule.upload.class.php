<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleUpload extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function uploadedFileExists($filedataName){
		return (($_FILES[$filedataName]['size'] > 0) && (strlen($_FILES[$filedataName]['tmp_name']) > 0));
	}
	
}

?>