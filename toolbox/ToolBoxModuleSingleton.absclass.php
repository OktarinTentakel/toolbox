<?php

/**
 * ToolBoxModuleSingleton is the default class for module based singletons in a module.
 * Besides the toplevel methods contained in each module, there may also be encapsulated singleton
 * classes, which fulfill more complex tasks bound in an object, without filling the toplevel space
 * with too much functionality and but retaining connections between methods which are normally used together.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage datatypes
 */
abstract class ToolBoxModuleSingleton {

	// ***
	protected function __construct(Array $args = null){}
	
	
	
	public static function get(Array $constructorArgs = null){
		if( is_null(static::$instance) ){
			$className = get_called_class();
			static::$instance = new $className($constructorArgs);
		}
			
		return static::$instance;
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	/**
	 * Throws a standard exception for missing configuration data for a singleton.
	 * Normally called in the constructor.
	 * 
	 * @param String $missingDataName describe which part is missing
	 * @throws Exception
	 */
	protected function throwMissingSingletonDataException($missingDataName){
		throw new Exception('ToolBoxException | singleton for "'.get_class($this).'" could not be constructed, missing data "'.$missingDataName.'"');
	}
	
	
	
	/**
	 * Throws a standard exception for missing ressources like PHP-extensions.
	 * 
	 * @param String $missingDataName describe which part is missing
	 * @throws Exception
	 */
	protected function throwMissingSingletonRessourceException($missingDataName){
		throw new Exception('ToolBoxException | singleton for "'.get_class($this).'" could not be constructed, missing ressource "'.$missingDataName.'"');
	}
	
}

?>