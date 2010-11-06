<?php

//--|CLASS----------

class ToolBox {
	
	// ***
	private static $instance = null;
	
	private $modules = array();
	
	
	
	public static function get(){
		if( is_null(self::$instance) ){
			self::$instance = new ToolBox();
		}
		
		return self::$instance;
	}
	// ***
	
	
	
	//--|SETTER----------
	
	private function setModule($moduleName, $constructorArgs){
		$className = 'ToolBoxModule'.$moduleName;
		$this->modules[$moduleName] = new $className($constructorArgs);
	}
	
	
	
	//--|GETTER----------
	
	public function getModule($moduleName, $constructorArgs){
		if( !$this->moduleLoaded($moduleName) ){
			require_once "ToolBoxModule.$moduleName.class.php";
			$this->setModule($moduleName, $constructorArgs);
		}
		
		return $this->modules[$moduleName];
	}
	
	
	
	//--|QUESTIONS----------
	
	private function moduleLoaded($moduleName){
		return isset($this->modules[$moduleName]);
	}
	
	
	
	//--|MODULE-LOADER----------
	
	public static function __callStatic($name, $arguments){
		return self::get()->getModule($name, $arguments);
	}
	
}

?>