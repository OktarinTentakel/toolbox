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
	
	public function __construct($moduleName, $addedArgs){
		$this->moduleName = $moduleName;
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	protected function throwModuleException($msg){
		throw new Exception('ToolBoxException | for module "'.$this->moduleName.'" > '.$msg);
	}
	
	
	
	protected static function throwSingletonRegisterException($className){
		throw new Exception('ToolBoxException | singleton for '.$className.' could not be registered');
	}
	
	
	
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