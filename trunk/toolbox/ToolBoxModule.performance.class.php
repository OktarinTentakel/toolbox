<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModulePerformance extends ToolBoxModule {
	
	const SINGLETON_SIMPLEFILEOUTPUTCACHE = 'SimpleFileOutputCache';
	const SINGLETON_SIMPLEAPCOUTPUTCACHE = 'SimpleApcOutputCache';
	public static $SINGLETON_CLASSES = array(self::SINGLETON_SIMPLEFILEOUTPUTCACHE, self::SINGLETON_SIMPLEAPCOUTPUTCACHE);

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
}



//--|NESTED-BASECLASS-[SimpleOutputCache]----------

abstract class SimpleOutputCache extends ToolBoxModuleSingleton {

	// ***
	protected $requestId = '';
	protected $disabled = false;
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		$uriId = substr(str_replace('/', '-', $_SERVER['REQUEST_URI']), 1);
		$this->requestId = (($uriId != '') ? $uriId : '_INDEX_').'-'.$this->getContext();
	}
	// ***
	
	//--|GETTER----------
	
	protected function getContext(){
		return md5(print_r($_POST, true));
	}
	
	
	
	//--|FUNCTIONALITY----------
	
	abstract public function cache($content);
	
	abstract public function display($content = null, $typeOverwrite = 'html');
	
	public function disable(){
		$this->disabled = true;
	}
	
	abstract public function flush();
	
	
	
	//--|QUESTIONS----------
	
	abstract public function hasCached();
	
	public function isEnabled(){
		return !$this->disabled;
	}
	
}



//--|NESTED-SINGLETON-[SimpleFileOutputCache]----------

class SimpleFileOutputCache extends SimpleOutputCache {
	const CACHE_DIR = 'CACHE_DIR';
	const FILE_SUFFIX = 'FILE_SUFFIX';
	const DEFAULT_FILE_SUFFIX = '.tmp';
	
	
	
	// ***
	public static $instance = null;
	
	private $cacheDir = '';
	private $fileSuffix = '';
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		if( isset($args[self::CACHE_DIR]) ){
			$this->cacheDir = ''.$args[self::CACHE_DIR];
			
			if( $this->cacheDir[strlen($this->cacheDir)-1] != '/' ){
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
				
				if( is_readable($this->cacheDir.$this->requestId.$this->fileSuffix) ){
					readfile($this->cacheDir.$this->requestId.$this->fileSuffix);
				} else {
					echo '';
				}
			}
		} else {
			header('content-type: text/'.$typeOverwrite.'; charset=utf-8');
			echo $content;
		}
	}
	
	
	
	public function flush(){
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
	
	public function hasCached(){
		return !$this->disabled ? is_readable($this->cacheDir.$this->requestId.$this->fileSuffix) : false;
	}
	
}



//--|NESTED-SINGLETON-[SimpleApcOutputCache]----------

class SimpleApcOutputCache extends SimpleOutputCache {
	const VAR_PREFIX = 'VAR_PREFIX';
	const DEFAULT_VAR_PREFIX = '';
	
	private static $NEEDED_APC_FUNCTIONS = array('apc_store', 'apc_fetch', 'apc_exists', 'apc_clear_cache', 'apc_cache_info');
	
	
	
	// ***
	public static $instance = null;
	
	private $varPrefix = '';
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		if( isset($args[self::VAR_PREFIX]) ){
			$this->varPrefix = ''.$args[self::VAR_PREFIX];
		} else {
			$this->varPrefix = self::DEFAULT_VAR_PREFIX;
		}
		
		foreach( self::$NEEDED_APC_FUNCTIONS as $apcFunction ){
			if( !function_exists($apcFunction) ){
				$this->throwMissingSingletonRessourceException($apcFunction);
			}
		}
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	public function cache($content){
		if( !$this->disabled ){
			apc_store($this->varPrefix.$this->requestId, $content);
		}
	}
	
	
	
	public function display($content = null, $typeOverwrite = 'html'){
		if( is_null($content) ){
			if( !$this->disabled ){
				header('content-type: text/'.$typeOverwrite.'; charset=utf-8');
				
				if( apc_exists($this->varPrefix.$this->requestId) ){
					echo apc_fetch($this->varPrefix.$this->requestId);
				} else {
					echo '';
				}
			}
		} else {
			header('content-type: text/'.$typeOverwrite.'; charset=utf-8');
			echo $content;
		}
	}
	
	
	
	public function flush(){
		$cacheInfo = apc_cache_info('user', true);
		apc_clear_cache('user');
		
		return $cacheInfo['num_entries'];
	}
	
	
	
	//--|QUESTIONS----------
	
	public function hasCached(){
		return !$this->disabled ? apc_exists($this->varPrefix.$this->requestId) : false;
	}
	
}

?>