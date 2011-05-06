<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModulePerformance extends ToolBoxModule {
	
	const SINGLETON_SIMPLEOUTPUTCACHE = 'SimpleOutputCache';
	public static $SINGLETON_CLASSES = array(self::SINGLETON_SIMPLEOUTPUTCACHE);

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
}



//--|NESTED-SINGLETON-[SimpleOutputCache]----------

class SimpleOutputCache extends ToolBoxModuleSingleton {
	const CACHE_DIR = 'CACHE_DIR';
	const FILE_SUFFIX = 'FILE_SUFFIX';
	const DEFAULT_FILE_SUFFIX = '.tmp';
	
	
	
	// ***
	public static $instance = null;
	
	
	private $cacheDir = null;
	private $requestId = null;
	private $fileSuffix = null;
	private $disabled = false;
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		$this->requestId = ''.mb_substr(str_replace('/', '-', $_SERVER['REQUEST_URI']), 1).'-'.$this->getContext();
		
		if( isset($args[self::CACHE_DIR]) ){
			$this->cacheDir = ''.$args[self::CACHE_DIR];
			
			if( $this->cacheDir[mb_strlen($this->cacheDir)-1] != '/' ){
				$this->cacheDir .= '/';
			}
		} else {
			$this->throwMissingSingletonDataException(self::CACHE_DIR);
		}
		
		if( isset($args[self::FILE_SUFFIX]) ){
			$this->fileSuffix = ''.$args[self::FILE_SUFFIX];
		} else {
			$this->fileSuffix = self::DEFAULT_FILE_SUFFIX;
		}
	}
	// ***
	
	
	
	//--|GETTER----------
	
	private function getContext(){
		return md5(print_r($_POST, true));
	}
	
	
	
	//--|FUNCTIONALITY----------
	
	public function cache($content){
		if( !$this->disabled ){
			file_put_contents($this->cacheDir.$this->requestId.$this->fileSuffix, $content);
		}
	}
	
	
	
	public function display($content = null, $typeOverwrite = 'html'){
		if( is_null($content) ){
			if( !$this->disabled ){
				header('content-type: text/'.$typeOverwrite.'; charset=utf-8');
				readfile($this->cacheDir.$this->requestId.$this->fileSuffix);
			}
		} else {
			header('content-type: text/'.$typeOverwrite.'; charset=utf-8');
			echo $content;
		}
	}
	
	
	
	public static function disable(){
		$this->disabled = true;
	}
	
	
	
	public static function flush(){
		$fileCount = 0;
		
		if( $handle = opendir($this->cacheDir) ){
			while( ($cacheFile = readdir($handle)) !== false ){
				if( ($cacheFile != '.') && ($cacheFile != '..') ){
					$fileCount++;
					@unlink($this->cacheDir.$cacheFile);
				}
			}
			
			closedir($handle);
		}
		
		return $fileCount;
	}
	
	
	
	//--|QUESTIONS----------
	
	public static function hasCached(){
		return !$this->disabled ? file_exists($this->cacheDir.$this->requestId.$this->fileSuffix) : false;
	}
	
	
	
	public static function isEnabled(){
		return !$this->disabled;
	}
	
}

?>