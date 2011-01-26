<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleArray extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function concat() {
		$vars = func_get_args();
		$array = array();
		foreach( $vars as $var ){
			if( is_array($var) ){
				foreach( $var as $val ){
					$array[] = $val;
				}
			} else {
				$array[] = $var;
			}
		}
		
		return $array;
	}
	
	
	
	public function assocToObject(Array $array){
		$res = new StdClass();
		
		foreach( $array as $key => $value ){
			$res->$key = $value;
		}
		
		return $res;
	}
	
}

?>