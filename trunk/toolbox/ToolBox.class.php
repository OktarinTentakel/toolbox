<?php

//--|CLASS----------

class ToolBox {
	
	// ***
	private static $instance = null;
	
	private $modules = array();
	private $singletons = array();
	
	
	
	public static function get(){
		if( is_null(self::$instance) ){
			self::$instance = new ToolBox();
		}
		
		return self::$instance;
	}
	// ***
	
	
	
	//--|SETTER----------
	
	public function setModule($moduleName, $constructorArgs){
		$className = 'ToolBoxModule'.$moduleName;
		$this->modules[$moduleName] = new $className($moduleName, $constructorArgs);
	}
	
	
	
	public function setSingleton($singletonClassName){
		$this->singletons[$singletonClassName] = $singletonClassName::get();
	}
	
	
	
	//--|GETTER----------
	
	public function getModule($moduleName, $constructorArgs){
		if( !$this->moduleLoaded($moduleName) ){
			require_once "ToolboxModule.$moduleName.class.php";
			$this->setModule($moduleName, $constructorArgs);
		}
		
		return $this->modules[$moduleName];
	}
	
	
	
	public function getSingleton($singletonClassName){
		return isset($this->singletons[$singletonClassName]) ? $this->singletons[$singletonClassName] : null;
	}
	
	
	
	//--|QUESTIONS----------
	
	private function moduleLoaded($moduleName){
		return isset($this->modules[$moduleName]);
	}
	
	
	
	private function singletonSet($singletonClassName){
		return isset($this->singletons[$singletonClassName]);
	}
	
	
	
	//--|MODULE-LOADER----------
	
	public static function __callStatic($name, $arguments){
		return self::get()->getModule($name, $arguments);
	}
	
	
	
	//--|SINGLETON-ACCESSOR----------
	
	public function __get($name){
		return self::get()->getSingleton($name);
	}
	
}

?>