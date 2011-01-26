<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleDatetime extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function dbDateToIsoDate($dbDate){
		return ($dbDate != '') ? date('Y-m-d', strtotime($dbDate)) : '';
	}
	
}

?>