<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleDownload extends ToolBoxModule {
	
	// ***
	public function __construct($args){
		parent::__construct($args);
	}
	// ***
	
	
	
	public function requestFile($file){
		if( file_exists($file) ){
			return true;
		} else {
			return false;
		}
	}

}

?>