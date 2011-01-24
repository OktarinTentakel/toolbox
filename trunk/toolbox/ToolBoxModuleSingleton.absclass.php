<?php


class ToolBoxModuleSingleton {
	
	protected function __construct(Array $args = null){}
	
	
	
	public static function get(Array $constructorArgs = null){
		if( is_null(static::$instance) ){
			$className = get_called_class();
			static::$instance = new $className($constructorArgs);
		}
			
		return static::$instance;
	}
	
	
	
	protected function throwMissingSingletonDataException($missingDataName){
		throw new Exception('ToolBoxException | singleton for "'.get_class($this).'" could not be constructed, missing data "'.$missingDataName.'"');
	}
}


?>