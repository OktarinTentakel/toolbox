<?php

//--|CLASS----------

/**
 * ToolBoxModule is the core carrier of the ToolBox functionality.
 * Each module encapsulates everything concerning a certain standard topic of development.
 * Simple helpers and everyday methods are found on the toplevel, while specific and complex
 * functionality are put into individually deployable singletons, which can be activated as needed.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package basic
 * @subpackage skeleton
 */
abstract class ToolBoxModule {

	// ***
	protected $moduleName;
	
	public function __construct($moduleName, Array $addedArgs = null){
		$this->moduleName = $moduleName;
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	/**
	 * Throws a standardized exception for a simple exception thrown within a module.
	 * 
	 * @throws Exception
	 */
	protected function throwModuleException($msg){
		throw new Exception('ToolBoxException | for module "'.$this->moduleName.'" > '.$msg);
	}
	
	
	
	/**
	 * Throws a standardized exception for a failed singleton registration.
	 * 
	 * @throws Exception
	 */
	protected static function throwSingletonRegisterException($className){
		throw new Exception('ToolBoxException | singleton for '.$className.' could not be registered');
	}
	
	
	
	/**
	 * Globally registers on of the nested module singletons for direct access under its class name.
	 * If no name is given, the first will be taken, if present.
	 * 
	 * A standard use case would be something like this:
	 * 
	 * If we would like to use the URL-routing capabilities of the routing module we can register the router singleton like this.
	 * ToolBox::_routing_()->registerSingleton(ToolBoxModuleRouting::SINGLETON_ROUTER);
	 * 
	 * After this we may access the router singleton like this.
	 * ToolBox::get()->Router;
	 * 
	 * Of course you could also get the singleton manually and manage it by yourself (but this could be potentially obfuscating the class source).
	 * Router::get();
	 * 
	 * @param unknown_type $className
	 * @param array $constructorArgs
	 */
	public function registerSingleton($className = '', Array $constructorArgs = null){
		if( is_array(static::$SINGLETON_CLASSES) && in_array($className, static::$SINGLETON_CLASSES) ){
			ToolBox::get()->setSingleton($className, $constructorArgs);
		} elseif( is_array(static::$SINGLETON_CLASSES) && (count(static::$SINGLETON_CLASSES) > 0) && ($className == '') ){
			$className = static::$SINGLETON_CLASSES[0];
			ToolBox::get()->setSingleton($className, $constructorArgs);
		} else {
			self::throwSingletonRegisterException($className);
		}
	}

}

?>