<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleFileSystem contains helper methods for dealing with server-files and directories.
 * While file handling is generally quite convenient, if not a little inconsistent, in PHP, this module
 * strives to ease some standard-tasks, that would result in some more complicated lines of code with every use
 * and to ease recursive operations a little bit. 
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage server
 */
class ToolBoxModuleFileSystem extends ToolBoxModule {

	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Checks if a directory contains any actual files.
	 *
	 * @param String $dir path of the directory to check
	 * @throws Exception if directory doesn't exits, is not readable or is actually no directory
	 * @return Boolean true / false
	 */
	public function isEmptyDirectory($dir, $ignoreHidden = true){
		if( is_dir($dir) && is_readable($dir) ){
			$count = 0;
			$files = opendir($dir);
			
			while( $file=readdir($files) ){
				$count++;
				if( $ignoreHidden && !in_array($file, array('.', '..')) && (strncmp($file, '.', 1) == 0) ){
					$count--;
				}
				
				if( $count > 2 ){
					return false;
				}
			}
			
			return true;
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, given dir no directory or not readable');
		}
	}
	
	
	
	/**
	 * Return the amount of files (not counting directories) contained in the given directory.
	 * Does not work recursive.
	 * 
	 * @param String $dir the path of the directory to count files in
	 * @throws Exception if directory doesn't exits, is not readable or is actually no directory
	 * @return uint amount of files in the directory
	 */
	public function countDirectoryFiles($dir, $ignoreHidden = true){
		if( is_dir($dir) && is_readable($dir) ){
			$dir = str_replace('//', '/', $dir.'/');
			$count = 0;
			$files = opendir($dir);
			
			while( $file=readdir($files) ){
				if( !is_dir($dir.$file) && (!$ignoreHidden || ($ignoreHidden && (strncmp($file, '.', 1) != 0))) ){
					$count++;
				}
			}
			
			return $count;
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, given dir no directory or not readable');
		}
	}
	
	
	
	/**
	 * Searches recursively for a certain filename in a directory and returns all found occurences as an
	 * array of paths where to find the file relative to the basepath.
	 *
	 * @param String $basepath the path to the directory to use as a starting point for the search
	 * @param String $filename the complete name of the file to search for, or a regex to match against
	 * @param Boolean $filenameAsRex defines if the filename is to be interpreted as a full name or a regex
	 * @throws Exception if basepath doesn't exits, is not readable or is actually no directory
	 * @return Array list of matching files found in the directory, with relative paths based on the basepath
	 */
	public function searchForFiles($basepath, $filename, $filenameAsRex = false){
		if( is_dir($basepath) && is_readable($basepath) ){
			$matches = array();
			
			$dirIterator = new RecursiveDirectoryIterator($basepath);
			$itIterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);
			
			foreach( $itIterator as $file ){
				if(
					$file->isFile()
					&& (
						(!$filenameAsRex &&	($filename == $file->getFilename()))
						|| ($filenameAsRex && preg_match($filename, $file->getFilename()))
					)
				){
					$matches[] = str_replace(array('\\', $basepath), array('/', ''), $file->getPathname());
				}
			}
			
			return $matches;
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, given dir no directory or not readable');
		}
	}
	
}

?>