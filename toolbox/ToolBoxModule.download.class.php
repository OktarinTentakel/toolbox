<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleDownload contains methods for managing everything concerning getting
 * a file from a server onto a client system.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage procedures
 */
class ToolBoxModuleDownload extends ToolBoxModule {
	
	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Tries to deliver a file from a defined filepath on the server with correct mime-type.
	 * 
	 * @param String $file a file path, including the filename itself, that should be delivered as a download
	 * @param String $mimeTypeOverwrite a valid mimetype to manually set instead of an application-based auto-value
	 * @throws Exception if file does not exists or is not readable by process
	 */
	public function requestFile($file, $mimeTypeOverwrite = null){
		if( file_exists($file) && is_readable($file) ){
			$pathInfo = pathinfo($file);
			
			if( is_null($mimeTypeOverwrite) ){
				header('Content-type: application/'.$pathInfo['extension']);
			} else {
				header('Content-type: '.$mimeTypeOverwrite);
			}
			header('Content-Disposition: attachment; filename="'.$pathInfo['basename'].'"');
			
			readfile($file);
		} else {
			$this->throwModuleException(__FUNCTION__.': io-error, file not found');
		}
	}

}

?>