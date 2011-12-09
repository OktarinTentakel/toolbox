<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModulePerformance contains methods for managing performace issues and to apply
 * standard solutions for speeding up processes and relieving the server of stress.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage procedures
 */
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

/**
 * SimpleOutputCache is an abstract implementation of an output cache to store random
 * byte-data in the cache under a specific and identifiable id.
 * 
 * The standard procedure for using a cache implementation is as follows:
 * - test for hasCached()
 * - if not cache(content)
 * - if so display()
 * The id under which to save the content is automatically generated from the current URI + request + post-data
 * and therefore automatically identifiable.
 * 
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package singletons
 * @subpackage procedures
 */
abstract class SimpleOutputCache extends ToolBoxModuleSingleton {

	// ***
	protected $uriId = '';
	protected $requestId = '';
	protected $disabled = false;
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		$this->uriId = substr(str_replace('/', '-', $_SERVER['REQUEST_URI']), 1);
		$this->requestId = (($uriId != '') ? $this->uriId : '_INDEX_').'-'.$this->getContext();
	}
	// ***
	
	//--|GETTER----------
	
	/**
	 * Returns the current data-context hashed to add to the URL as a status identifier.
	 * 
	 * @return String the hashed current request context
	 */
	protected function getContext(){
		return md5(print_r($_POST, true));
	}
	
	
	
	//--|FUNCTIONALITY----------
	
	/**
	 * Caches the given content at the currently calculated id.
	 * 
	 * @param String $content the content-string to cache.
	 */
	abstract public function cache($content);
	
	
	
	/**
	 * Outputs the content identified by the currently calculated id if present.
	 * The output is html by default, but may be overwritten.
	 * 
	 * @param String $content overwrite content to use instead of the cache-value, could also be used as fallback by implementations
	 * @param String $typeOverwrite a mime-type to use instead of html
	 */
	abstract public function display($content = null, $typeOverwrite = 'html');
	
	
	
	/**
	 * Disables the cache, no matter what further method calls are fired.
	 */
	public function disable(){
		$this->disabled = true;
	}
	
	
	
	/**
	 * Flushes the cache, effectively deleting all cached content.
	 */
	abstract public function flush();
	
	
	
	//--|QUESTIONS----------
	
	/**
	 * Returns if cache data exists under the currently calculated id.
	 * 
	 * @return Boolean true/false
	 */
	abstract public function hasCached();
	
	
	
	/**
	 * Returns if the cache is enabled at the moment.
	 * 
	 * @return Boolean true/false
	 */
	public function isEnabled(){
		return !$this->disabled;
	}
	
}



//--|NESTED-SINGLETON-[SimpleFileOutputCache]----------

/**
 * A file-based implementation of an output cache.
 * Saves the contents in separate files for each context.
 * To reduce probable conflicts with maximum file count per directory, each defined URL gets it's own subdir.
 * 
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package singletons
 * @subpackage procedures
 */
class SimpleFileOutputCache extends SimpleOutputCache {
	
	const CACHE_DIR = 'CACHE_DIR';
	const FILE_SUFFIX = 'FILE_SUFFIX';
	const DEFAULT_FILE_SUFFIX = '.tmp';
	
	
	
	// ***
	public static $instance = null;
	
	private $baseCacheDir = '';
	private $cacheDir = '';
	private $fileSuffix = '';
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		if( isset($args[self::CACHE_DIR]) ){
			$this->cacheDir = ''.$args[self::CACHE_DIR];
			
			if( $this->cacheDir[strlen($this->cacheDir)-1] != '/' ){
				$this->cacheDir .= '/';
				$this->baseCacheDir = $this->cacheDir;
			}
			
			if( is_dir($this->cacheDir) && is_readable($this->cacheDir) ){
				$this->cacheDir .= $this->uriId.'/';
				
				if( !file_exists($this->cacheDir) ){
					if( !mkdir($this->cacheDir) ){
						$this->throwMissingSingletonRessourceException($this->cacheDir.' not accessible');
					}
				}
			} else {
				$this->throwMissingSingletonRessourceException($this->cacheDir.' not accessible');
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
	
	/**
	 * Caches the given content into a byte-file in the current cache-dir.
	 * Does nothing if cache is disabled.
	 * 
	 * @see SimpleOutputCache::cache()
	 * @param String $content the content to cache for the current context
	 */
	public function cache($content){
		if( !$this->disabled ){
			file_put_contents($this->cacheDir.$this->requestId.$this->fileSuffix, $content);
		}
	}
	
	
	
	/**
	 * Displays the content of a cached file for the current context.
	 * Doesn't do anything if cache is disabled, except for the case when a content overwrite is
	 * defined since this is normally used for debugging cases.
	 * 
	 * @see SimpleOutputCache::display()
	 * @param String $content overwrite content to display instead of the actual content
	 * @param String $typeOverwrite mime-type overwrite to instead of the default "html"
	 */
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
	
	
	
	/**
	 * Removes all cache files and therefore effectively empties the whole thing. 
	 * 
	 * @see SimpleOutputCache::flush()
	 * @return unit the number of removed entries
	 */
	public function flush(){
		$fileCount = 0;
		
		if( $handle = opendir($this->baseCacheDir) ){
			while( ($cacheFile = readdir($handle)) !== false ){
				if( is_dir($cacheFile) && is_readable($cacheFile) && ($subHandle = opendir($this->baseCacheDir.$cacheFile.'/')) ){
					while( ($subCacheFile = readdir($subHandle)) !== false ){
						if( ($subCacheFile != '.') && ($subCacheFile != '..') ){
							$fileCount++;
							@unlink($this->baseCacheDir.$cacheFile.'/'.$subCacheFile);
						}
					}
					
					closedir($subHandle);
				}
			}
			
			closedir($handle);
		}
		
		return $fileCount;
	}
	
	
	
	//--|QUESTIONS----------
	
	/**
	 * Returns if the current context is already cached.
	 * Depends on existent subdir and file.
	 * If cache is disabled, this method will always return false
	 * 
	 * @see SimpleOutputCache::hasCached()
	 * @return Boolean true/false
	 */
	public function hasCached(){
		return !$this->disabled ? is_readable($this->cacheDir.$this->requestId.$this->fileSuffix) : false;
	}
	
}



//--|NESTED-SINGLETON-[SimpleApcOutputCache]----------

/**
 * An apc-based implementation of an output cache.
 * Saves the contents in user-cache entries.
 * Adds the concept of prefixes for the entry-names to be able of differntiate different sites and
 * datatypes in the shared cache space.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package singletons
 * @subpackage procedures
 */
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
	
	/**
	 * Caches the given content into a user-cache-entry.
	 * Does nothing if cache is disabled.
	 *
	 * @see SimpleOutputCache::cache()
	 * @param String $content the content to cache for the current context
	 */
	public function cache($content){
		if( !$this->disabled ){
			apc_store($this->varPrefix.$this->requestId, $content);
		}
	}
	
	
	
	/**
	 * Displays the content of a user-cache entry for the current context.
	 * Doesn't do anything if cache is disabled, except for the case when a content overwrite is
	 * defined since this is normally used for debugging cases.
	 *
	 * @see SimpleOutputCache::display()
	 * @param String $content overwrite content to display instead of the actual content
	 * @param String $typeOverwrite mime-type overwrite to instead of the default "html"
	 */
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
	
	
	
	/**
	 * Removes all user-cache-entries and therefore effectively empties the whole user cache.
	 * This implementation does not yet use the APCIterator, since the status of the class doesn't seem
	 * to be stable yet and might be unavailable, even with a recent APC-version.
	 *
	 * @see SimpleOutputCache::flush()
	 * @param String $varPrefix a prefix to consider while flushing, if set, only entries having the prefix will be deleted
	 * @return unit the number of removed entries
	 */
	public function flush($varPrefix = null){
		if( is_null($varPrefix) ){
			$cacheInfo = apc_cache_info('user', true);
			apc_clear_cache('user');
			
			return $cacheInfo['num_entries'];
		} else {
			$cacheInfo = apc_cache_info('user', true);
			$deletedCount = 0;
			
			foreach($cacheInfo['cache_list'] as $cacheEntry){
				if( strpos($cacheEntry['info'], $varPrefix) === 0 ){
					apc_delete($cacheEntry['info']);
					$deletedCount++;
				}
			}
			
			return $deletedCount;
		}
	}
	
	
	
	//--|QUESTIONS----------
	
	/**
	 * Returns if the current context is already cached.
	 * Depends on existent user-cache-entry.
	 * If cache is disabled, this method will always return false
	 *
	 * @see SimpleOutputCache::hasCached()
	 * @return Boolean true/false
	 */
	public function hasCached(){
		return !$this->disabled ? apc_exists($this->varPrefix.$this->requestId) : false;
	}
	
}

?>