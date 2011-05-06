<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleFilesystem extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function isEmptyDirectory($dir){
		$count = 0;
		if( is_dir($dir) ){
			$files = opendir($dir);
			
			while( $file=readdir($files) !== false ){
				$count++;
				if( $count > 2 ){
					return false;
				}
			}
			
			return true;
		} else {
			return false;
		}
	}
	
	
	
	public function searchForFiles($basepath, $filename){
		$matches = array();
		
		$dirIterator = new RecursiveDirectoryIterator($basepath);
		$itIterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);
		
		foreach( $itIterator as $file ){
			if( $file->isFile() && ($filename == $file->getFilename()) ){
				$matches[] = str_replace(array('\\', $basepath), array('/', ''), $file->getPathname());
			}
		}
		
		return $matches;
	}
	
}

?>