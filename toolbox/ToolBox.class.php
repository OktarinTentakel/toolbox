<?php

//--|CLASS----------

class ToolBox {
	
	// ***
	private static $instance = null;
	
	private $modules = array();
	
	private function __construct(){
	
	}
	
	
	
	public static function get(){
		if( is_null(self::$instance) ){
			self::$instance = new ToolBox();
		}
		
		return self::$instance;
	}
	// ***
	
	
	
	//--|SETTER----------
	
	private function setModule($moduleName){
		$className = 'ToolBox'.$moduleName;
		$this->modules[$moduleName] = new $className;
	}
	
	
	
	//--|GETTER----------
	
	public function getModule($moduleName){
		if( !$this->moduleLoaded($moduleName) ){
			require_once "$moduleName/ToolBox.$moduleName.class.php";
			$this->setModule($moduleName);
		}
		
		return $this->modules[$moduleName];
	}
	
	
	
	//--|QUESTIONS----------
	
	private function moduleLoaded($moduleName){
		return isset($this->modules[$moduleName]);
	}
	
	
	
	//--|MODULE-LOADERS----------
	
	public static function download(){
		return self::get()->getModule(__FUNCTION__);
	}
	
}

?>