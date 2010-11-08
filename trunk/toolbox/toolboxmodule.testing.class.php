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
		$this->name = "$name";
	}
	
	
	
	public static function get($name){
		return new ToolBoxTestSuite($name);
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	public function addModule(ToolBoxTestModule $module){
		$this->modules[] = $module;
		return $this;
	}
}



class ToolBoxTestModule {
	// ***
	private $name = '';
	private $cases = array();
	
	private function __construct($name){
		$this->name = "$name";
	}
	
	
	
	public static function get(){
		return new ToolBoxTestModule();
	}
	// ***
	
	
	
	//	--|FUNCTIONALITY----------
	
	public function addCase(ToolBoxTestModule $module){
		$this->modules[] = $module;
		return $this;
	}
}



class ToolBoxTestCase {
	// ***
	private $name = '';
	private $value = null;
	private $result = null;
	private $message = '';
	
	private function __construct($name, $value, $msg){
		$this->name = "$name";
		$this->value = $value;
		$this->message = "$msg";
	}
	
	
	
	public static function get($name, $value, $msg){
		return new ToolBoxTestCase($name, $value, $msg);
	}
	// ***
	
	
	
	//--|SETTER----------
	
	public function setEquals($checkVal){
		if( is_null($result) ){
			$this->result = print_r($this->value, true) == print_r($checkVal, true);
		}
	}
	
	
	
	public function setMatchesRegEx($regEx, $mods){
		if( is_null($result) ){
			return preg_match('/'.$regEx.'/'.$mods, $this->value);
		}
	}
	
	
	
	public function setIsEmpty(){
		if( is_null($result) ){
			return empty($this->value);
		}
	}
	
	
	
	public function setIsNull(){
		if( is_null($result) ){
			return is_null($this->value);
		}
	}
	
	
	
	public function setIsCustom($result){
		$this->result = $result;
	}
	
	
	
	//--|GETTER----------
	
	public function getResult(){
		return $this->result;
	}
}

?>