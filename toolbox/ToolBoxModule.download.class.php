<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleDownload extends ToolBoxModule {
	
	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function requestFile($file){
		if( file_exists($file) ){
			$pathInfo = pathinfo($file);
			
			header('Content-type: application/'.$pathInfo['extension']);
			header('Content-Disposition: attachment; filename="'.$pathInfo['basename'].'"');
			
			readfile($file);
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, file not found');
		}
	}

}

?>