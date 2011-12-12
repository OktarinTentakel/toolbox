<?php

//--|CLASS----------

/**
 * ToolBox is a loose collection of algorithms and procedures needed on a regular basis
 * while developing with PHP5. The whole framework is module based, with each module representing a
 * certain topic of development (validation or routing e.g.).
 * 
 * ToolBox tries to be a complete hub of random functionality, without the need to organize anything
 * coming from the framework yourself.
 * 
 * By default ToolBox doesn't load anything but it's bare self on include. Instead ToolBox employs auto-loading
 * for each used module dynamically on runtime. Additional to loading modules, with static-like toplevel methods
 * ToolBox also provides complete solutions such as an URL-router as nested singleton to module, which may be registered
 * with the framework, for further use.
 * 
 * Here are some examples to enlighten you on how this is thought to be:
 * 
 * require_once 'toolbox/ToolBox.class.php';					// just inlude the main file, nothing big happening here
 * 
 * ToolBox::_string_()->setUtf8Environment();					// string-module being loaded, method executed
 * 
 * $trunc = ToolBox::_string_()->truncate('abc');				// string module already loaded, just method used to truncate string
 * 
 * ToolBox::_routing_()->registerSingleton(ToolBoxModuleRouter::SINGLETON_ROUTER);	// rounting module being loaded, router-singleton registered
 * 
 * ToolBox::get()->Router->addShortRule('(.*)', /print_r/a);	// work with the router singleton
 * 
 * This way you can work with the functionalities completely encapsulated in ToolBox, without creating any doubts where
 * which methods originate. 
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package basic
 * @subpackage skeleton
 */
class ToolBox {
	
	// ***
	private static $instance = null;
	
	private $modules = array();
	private $singletons = array();
	
	
	
	public static function get($moduleName = null){
		if( is_null($moduleName) ){
			if( is_null(self::$instance) ){
				self::$instance = new ToolBox();
			}
			
			return self::$instance;
		} else {
			$staticMethodName = '_'.$moduleName.'_';
			$constructorArgs = func_get_args();
			array_shift($constructorArgs);
			
			return call_user_func_array(array('self', $staticMethodName), $constructorArgs);
		}
	}
	// ***
	
	
	
	//--|SETTER----------
	
	/**
	 * Registers a ToolBox-class instance as a module to ToolBox by class name.
	 * 
	 * @param String $moduleName the name of the module to register, this is NOT a class name, ToolBoxModule gets added by default 
	 * @param Array $constructorArgs additional constructor arguments for the module itself
	 */
	public function setModule($moduleName, Array $constructorArgs = null){
		$className = 'ToolBoxModule'.$moduleName;
		$this->modules[$moduleName] = new $className($moduleName, $constructorArgs);
	}
	
	
	
	/**
	 * Registers a module-nested singleton to the main scope of ToolBox.
	 * Takes a singleton name from a module an sets up the instance for access from the ToolBox-singleton itself.
	 * 
	 * @param String $singletonClassName name of the singleton to register, normally a constant from the corresponding module
	 * @param Array $constructorArgs additional parameters for the singleton constructor
	 */
	public function setSingleton($singletonClassName, Array $constructorArgs = null){
		$this->singletons[$singletonClassName] = $singletonClassName::get($constructorArgs);
	}
	
	
	
	//--|GETTER----------
	
	/**
	 * Returns a module instance by name.
	 * 
	 * @param String $moduleName the name of the module to return.
	 * @param Array $constructorArgs additional parameters for the module's constructor in case the module is not already instantiated
	 * @return ToolBoxModule the requested module
	 */
	public function getModule($moduleName, Array $constructorArgs = null){
		if( !$this->moduleLoaded($moduleName) ){
			require_once "ToolBoxModule.$moduleName.class.php";
			$this->setModule($moduleName, $constructorArgs);
		}
		
		return $this->modules[$moduleName];
	}
	
	
	
	/**
	 * Returns a singleton registered to ToolBox by it's classname.
	 * 
	 * @param String $singletonClassName the name of the singleton class of which to return the registered instance
	 * @return ToolBoxModuleSingleton the requested singleton
	 */
	public function getSingleton($singletonClassName){
		return isset($this->singletons[$singletonClassName]) ? $this->singletons[$singletonClassName] : null;
	}
	
	
	
	//--|QUESTIONS----------
	
	/**
	 * Returns if a module of a certain name has already been loaded, due to use or manual instantiation.
	 * 
	 * @param String $moduleName the module name to check for
	 * @return Boolean true/false
	 */
	private function moduleLoaded($moduleName){
		return isset($this->modules[$moduleName]);
	}
	
	
	
	/**
	 * Returns if a singleton of a certain class name has already been registered.
	 * 
	 * @param String $singletonClassName the class name of the singleton to check for
	 * @return Boolean true/false
	 */
	private function singletonSet($singletonClassName){
		return isset($this->singletons[$singletonClassName]);
	}
	
	
	
	//--|MODULE-LOADER----------
	
	/**
	 * Dynamic module loading function based on magic method __callStatic.
	 * To streamline the module request syntax, this method takes module names as static
	 * method calls and returns the appropriate object, even loading an instantiating the module
	 * if this has not already happened.
	 * 
	 * @param String $name the name of the module to return
	 * @param Array $arguments additional arguments, which are delegated to the modules constructor on first call
	 * @return ToolBoxModule the requested module
	 */
	public static function __callStatic($name, $arguments){
		return self::get()->getModule(trim($name, '_'), $arguments);
	}
	
	
	
	//--|SINGLETON-ACCESSOR----------
	
	/**
	 * Dynamic singleton getter based on magic method __get.
	 * If a singleton has been registered to ToolBox from a module, the singleton may be
	 * accessed via this method by simply accessing a public property on the ToolBox-instance,
	 * having the classname of the registered singleton.
	 * 
	 * @param String $name the classname of the registered singleton
	 * @return ToolBoxModuleSingleton the requested singleton or null
	 */
	public function __get($name){
		return self::get()->getSingleton($name);
	}
	
}

?>