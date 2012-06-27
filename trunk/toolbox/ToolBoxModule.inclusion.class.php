<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleInclusion contains helper methods for managing problems of nested file-inclusions
 * and related problems concerning the inclusion and requirement of php-files.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage system
 */
class ToolBoxModuleInclusion extends ToolBoxModule {

	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Returns the string content of an included file.
	 * Does not check for anything, does a raw include, grabs the output buffer and returns the content.
	 *
	 * @param String $include the include to grab, all include paths are viable
	 * @return String the contents of the include
	 */
	function getIncludeContents($include) {
		if( is_file($include) ){
			ob_start();
			include $include;
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, inclusion cannot be located');
		}
	}

}

?>