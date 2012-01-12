<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleUpload offers simplified functions for dealing with uploads
 * and integrating client files into a server context.
 * 
 * The toplevel functions of this module should be all that is needed for managing standard upload tasks.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage procedures
 */
class ToolBoxModuleUpload extends ToolBoxModule {
	
	const MAX_FILENAME_LENGTH = 260;
	public static $MAX_SIZE;
	public static $CONTENT_SIZE;

	
	
	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
		
		$sizeUnit = strtoupper(substr(ini_get('post_max_size'), -1));
		$unitMultiplier =
			($sizeUnit == 'M')
				? 1048576
				: 	($sizeUnit == 'K')
					? 1024
					: 	($sizeUnit == 'G')
						? 1073741824
						: 1
		;
		
		self::$MAX_SIZE = intval(ini_get('post_max_size')) * $unitMultiplier;
		self::$CONTENT_SIZE = isset($_SERVER['CONTENT_LENGTH']) ? intval($_SERVER['CONTENT_LENGTH']) : null;
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Checks if a file under a certain data name is available in PHPs stack
	 * for uploaded files.
	 * 
	 * @param String $filedataName the filedata name to check for
	 * @return Boolean true/false
	 */
	public function uploadedFileExists($filedataName){
		return(
			isset($_FILES[$filedataName]['tmp_name'])
			&& (strlen($_FILES[$filedataName]['tmp_name']) > 0)  
			&& ($_FILES[$filedataName]['size'] > 0) 
			&& ($_FILES[$filedataName]['error'] == UPLOAD_ERR_OK)
		);
	}
	
	
	
	/**
	 * Integrates a file from PHPs uploaded files stack into a destination path,
	 * while checking for technical correctness and applying name rules.
	 * 
	 * @param String $destinationPath the local directory to move the uploaded file to
	 * @param String $filedataName the filedata name of the uploaded file
	 * @param Array $whitelist an array of strings containing allowed file endings
	 * @param uint $maxFileSizeMegabytes maximum allowed megabytes for the uploaded file
	 * @param Closure $nameCreationCallback a function to adapt the filename according to a certain algorithm, this function takes the old name as a parameter
	 * @param Boolean $sanitizeFileName defines if the filename should be roughly sanitized be removing incompatible characters and transforming others
	 * @throws Exception on technical issues and validation (POST exceeded, no upload found, missing upload filename, invalid extension, invalid filename, file already present, missing rights)
	 * @return Array information about the uploaded file having the following format: array('location' => '/abc/file.file', 'filename' => 'file.file')
	 */
	public function uploadFile(
		$destinationPath,
		$filedataName,
		Array $whitelist,
		$maxFileSizeMegabytes = 5,
		Closure $nameCreationCallback = null,
		$sanitizeFileName = true
	){
		if(
			(self::$CONTENT_SIZE > self::$MAX_SIZE)
			&& (self::$MAX_SIZE > 0)  
		){
			throw new Exception('POST exceeded maximum allowed size. (allowed: '.self::$MAX_SIZE.', POST: '.self::$CONTENT_SIZE);
		}
	
		// general error handling
		$uploadErrors = array(
	        0 => 'no error, the file uploaded with success',
	        1 => 'uploaded file exceeds the upload_max_filesize directive in php.ini',
	        2 => 'uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
	        3 => 'uploaded file was only partially uploaded',
	        4 => 'no file was uploaded',
	        6 => 'missing temporary folder'
		);

		if( !isset($_FILES[$filedataName]) ){
			throw new Exception('no upload found in \$_FILES for '.$filedataName);
		} elseif( isset($_FILES[$filedataName]['error']) && ($_FILES[$filedataName]['error'] > 0) ){
			throw new Exception($uploadErrors[$_FILES[$filedataName]['error']]);
		} elseif( !$this->uploadedFileExists($filedataName) ){
			throw new Exception('failed uploadedFileExists().');
		} elseif( !isset($_FILES[$filedataName]['name']) ){
			throw new Exception('file has no name.');
		}
	
		// size error handling
		$fileSize = @filesize($_FILES[$filedataName]['tmp_name']);
		if( ($fileSize == 0) || ($fileSize > ($maxFileSizeMegabytes * 1024 * 1024)) ){
			throw new Exception('filesize is zero or exceeds the maximum allowed size');
		}

		// whitelisting
		$pathInfo = pathinfo($_FILES[$filedataName]['name']);
		if( !in_array($pathInfo['extension'], $whitelist) ){
			throw new Exception('invalid file extension');
		}
	
		// sanitize filename
		$sanitizedFilename = 
			$sanitizeFileName 
				? $this->sanitizeFilename($pathInfo['filename'], $pathInfo['extension']) 
				: $pathInfo['basename']
		;
		if( 
			(strlen($sanitizedFilename) == (strlen($pathInfo['extension'])+1)) 
			|| ((strlen($sanitizedFilename) > self::MAX_FILENAME_LENGTH)) 
		){
			throw new Exception('invalid filename, no usable characters or too long');
		}
		
		$serverFile = array(
			'location' => 
				$destinationPath.(
					!is_null($nameCreationCallback) 
					? $nameCreationCallback($sanitizedFilename)
					: $sanitizedFilename
				)
			,
			'filename' => $sanitizedFilename
		);

		// prevent overwriting existing file
		if( file_exists($serverFile['location']) ){
			throw new Exception('file already exists');
		}

		// process file
		if( !@move_uploaded_file($_FILES[$filedataName]['tmp_name'], $serverFile['location']) ){
			throw new Exception('file could not be saved, check write rights for PHP in target dir: '.$serverFile['location']);
		}
	
		if( !chmod($serverFile['location'], 0775) ){
			throw new Exception('could not set access rights');
		}
		
		return $serverFile;
	}
	
	
	
	/**
	 * Standard method for integrating image files based on uploadFile.
	 * 
	 * @param String $destinationPath the local directory to move the uploaded file to
	 * @param String $filedataName the filedata name of the uploaded file
	 * @param uint $maxFileSizeMegabytes maximum allowed megabytes for the uploaded file
	 * @param Closure $nameCreationCallback a function to adapt the filename according to a certain algorithm, this function takes the old name as a parameter
	 * @param Boolean $sanitizeFileName defines if the filename should be roughly sanitized be removing incompatible characters and transforming others
	 * @throws Exception on technical issues and validation (POST exceeded, no upload found, missing upload filename, invalid extension, invalid filename, file already present, missing rights)
	 * @return Array information about the uploaded file having the following format: array('location' => '/abc/file.file', 'filename' => 'file.file')
	 */
	public function uploadImage(
		$destinationPath,
		$filedataName,
		$maxFileSizeMegabytes = 2,
		Closure $nameCreationCallback = null,
		$sanitizeFileName = true
	){
		return $this->uploadFile(
		$destinationPath,
		$filedataName,
		array('jpg', 'png', 'gif'),
		$maxFileSizeMegabytes,
		$nameCreationCallback,
		$sanitizeFileName
		);
	}
	
	
	
	/**
	 * Removes all generally unwanted characters from filenames and replaces some
	 * easily replaceable characters like umlauts and characters with french accents.
	 * 
	 * @param String $fileName the filename to sanitize
	 * @param String $extensionToIgnore defines an extension to ignore while sanitizing
	 */
	public function sanitizeFilename($fileName, $extensionToIgnore = null){
		$usesUnicode = ($fileName == utf8_encode($fileName));
		
		if( is_null($extensionToIgnore) ){
			$pathInfo = pathinfo($fileName);
			$extensionToIgnore = $pathInfo['extension'];
		}
		
		if( !empty($extensionToIgnore) ){
			$fileName = preg_replace('/\.'.$extensionToIgnore.'$/'.($usesUnicode ? 'u' : ''), '', $fileName);
		} else {
			$extensionToIgnore = '';
		}
		
		$map = array(
			'/\./'.($usesUnicode ? 'u' : '') => '_',
			'/=(\s+)=/'.($usesUnicode ? 'u' : '') => '_',
			'/ä/'.($usesUnicode ? 'u' : '') => 'ae',
			'/Ä/'.($usesUnicode ? 'u' : '') => 'Ae',
			'/ö/'.($usesUnicode ? 'u' : '') => 'oe',
			'/Ö/'.($usesUnicode ? 'u' : '') => 'Oe',
			'/Ü/'.($usesUnicode ? 'u' : '') => 'Ue',
			'/ü/'.($usesUnicode ? 'u' : '') => 'ue',
			'/ß/'.($usesUnicode ? 'u' : '') => 'ss',
			'/à/'.($usesUnicode ? 'u' : '') => 'a',
			'/á/'.($usesUnicode ? 'u' : '') => 'a',
			'/â/'.($usesUnicode ? 'u' : '') => 'a',
			'/é/'.($usesUnicode ? 'u' : '') => 'e',
			'/è/'.($usesUnicode ? 'u' : '') => 'e',
			'/ê/'.($usesUnicode ? 'u' : '') => 'e'
		);
		
		$saneName = preg_replace(array_keys($map), array_values($map), $fileName);

		return preg_replace('/[^a-zA-Z0-9_-]/'.($usesUnicode ? 'u' : ''), '', $saneName).'.'.$extensionToIgnore;
	}
	
}

?>