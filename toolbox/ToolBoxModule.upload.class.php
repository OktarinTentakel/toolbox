<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleUpload extends ToolBoxModule {
	
	const MAX_FILENAME_LENGTH = 260;
	public static $MAX_SIZE;
	public static $CONTENT_SIZE;

	// ***
	public function __construct($moduleName, $addedArgs){
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
		self::$CONTENT_SIZE = intval($_SERVER['CONTENT_LENGTH']);
	}
	// ***
	
	
	
	public function uploadedFileExists($filedataName){
		return(
			isset($_FILES[$filedataName]['tmp_name'])
			&& (strlen($_FILES[$filedataName]['tmp_name']) > 0)  
			&& ($_FILES[$filedataName]['size'] > 0) 
		);
	}
	
	
	
	public function uploadImage($destinationPath, $filedataName, $maxFileSizeMegabytes = 2, Closure $nameCreationCallback = null){
		return $this->uploadFile($destinationPath, $filedataName, array('jpg', 'png', 'gif'), $maxFileSizeMegabytes, $nameCreationCallback);
	}
	
	
	
	public function uploadFile($destinationPath, $filedataName, Array $whitelist, $maxFileSizeMegabytes = 5, Closure $nameCreationCallback = null){
		if(
			(self::$CONTENT_SIZE > self::$MAX_SIZE) 
			&& (self::$MAX_SIZE > 0)  
		){
			header("HTTP/1.1 500 Internal Server Error");
			die('POST exceeded maximum allowed size. (allowed: '.self::$MAX_SIZE.', POST: '.self::$CONTENT_SIZE);
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
			header('HTTP/1.1 500 Internal Server Error');
			die('no upload found in \$_FILES for '.$filedataName);
		} elseif( isset($_FILES[$filedataName]['error']) && ($_FILES[$filedataName]['error'] > 0) ){
			header('HTTP/1.1 500 Internal Server Error');
			die($uploadErrors[$_FILES[$filedataName]['error']]);
		} elseif( !$this->uploadedFileExists($filedataName) ){
			header('HTTP/1.1 500 Internal Server Error');
			die('failed uploadedFileExists().');
			exit(0);
		} elseif( !isset($_FILES[$filedataName]['name']) ){
			header('HTTP/1.1 500 Internal Server Error');
			die('file has no name.');
		}
	
		// size error handling
		$fileSize = @filesize($_FILES[$filedataName]['tmp_name']);
		if( ($fileSize == 0) || ($fileSize > ($maxFileSizeMegabytes * 1024 * 1024)) ){
			header('HTTP/1.1 500 Internal Server Error');
			die('filesize is zero or exceeds the maximum allowed size');
		}

		// whitelisting
		$pathInfo = pathinfo($_FILES[$filedataName]['name']);
		if( !in_array($pathInfo['extension'], $whitelist) ){
			header('HTTP/1.1 500 Internal Server Error');
			die('invalid file extension');
		}
	
		// sanitize filename
		$sanitizedFilename = $this->sanitizeFilename($pathInfo['filename'], $pathInfo['extension']);
		if( 
			(strlen($sanitizedFilename) == (strlen($pathInfo['extension'])+1)) 
			|| ((strlen($sanitizedFilename) > self::MAX_FILENAME_LENGTH)) 
		){
			header('HTTP/1.1 500 Internal Server Error');
			die('invalid filename, no usable characters or too long');
		}
		
		$serverFile = array(
			'location' => 
				$destinationPath.(
					!is_null($nameCreationCallback) 
					? $nameCreationCallback($sanitizedFilename).'.'.$pathInfo['extension'] 
					: $sanitizedFilename
				)
			,
			'filename' => $sanitizedFilename
		);

		// prevent overwriting existing file
		if( file_exists($serverFile['location']) ){
			header('HTTP/1.1 500 Internal Server Error');
			die('file already exists');
		}

		// process file
		if( !@move_uploaded_file($_FILES[$filedataName]['tmp_name'], $serverFile['location']) ){
			header('HTTP/1.1 500 Internal Server Error');
			die('file could not be saved, check write rights for PHP in target dir: '.$serverFile['location']);
		}
	
		if( !chmod($serverFile['location'], 0775) ){
			header('HTTP/1.1 500 Internal Server Error');
			die('could not set access rights');
		}
		
		return $serverFile;
	}
	
	
	
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