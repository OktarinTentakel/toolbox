<?php

//--|INCLUDES----------

require_once 'toolboxmodule.absclass.php';



//--|CLASS----------

class ToolBoxModuleTesting extends ToolBoxModule {
	
	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function evalSuite(){
		
	}
	
	
	
	public function evalModule(){
		
	}
	
	
	
	public function evalCase(){
		
	}

}



//--|NESTED----------

class ToolBoxTestSuite {
	// ***	
	private $name = '';
	private $modules = array();
	
	private function __construct($name){
		$this->name = $name;
	}
	
	
	
	public static function get($name){
		return new ToolBoxTestSuite($name);
	}
	
	
	
	public function addModule(ToolBoxTestModule $module){
		$this->modules[] = $module;
		return $this;
	}
	// ***
}



class ToolBoxTestModule {
	// ***
	private $cases = array();
	
	private function __construct(){}
	
	
	
	public static function get(){
		return new ToolBoxTestModule();
	}
	// ***
}



class ToolBoxTestCase {
	// ***
	private function __construct(){}
	
	
	
	public static function get(){
		return new ToolBoxTestCase();
	}
	// ***
}

?>