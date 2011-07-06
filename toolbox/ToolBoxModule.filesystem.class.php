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
		if( is_dir($dir) ){
			$count = 0;
			$files = opendir($dir);
			
			while( $file=readdir($files) ){
				$count++;
				if( $count > 2 ){
					return false;
				}
			}
			
			return true;
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, given dir no directory or not readable');
		}
	}
	
	
	
	public static function countDirectoryFiles($dir){
		if( is_dir($dir) ){
			$count = 0;
			$files = opendir($dir);
			
			while( $file=readdir($files) ){
				if( !is_dir($file) && (strncmp($file, '.', 1) != 0) ){
					$count++;
				}
			}
			
			return $count;
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, given dir no directory or not readable');
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