<?php

//--|CLASS----------

abstract class ToolBoxModule {

	// ***
	protected $moduleName;
	
	public function __construct($moduleName, $addedArgs){
		$this->moduleName = $moduleName;
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	protected function throwModuleException($msg){
		throw new Exception('ToolBoxException | for module "'.$this->moduleName.'" > '.$msg);
	}

}

?>