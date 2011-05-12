<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleVariable extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function applyRuleToValues(Closure $rule, Array $values, $negation = false){
		$res = true;
		
		foreach( $values as $val ){
			$res = $res && ($negation ? !$rule($val) : $rule($val));
			if( !$res ){
				break;
			}
		}
		
		return $res;
	}
	
	
	
	public function isNull(){
		return $this->applyRuleToValues(function($val){ return is_null($val); }, func_get_args());
	}
	
	
	
	public function isNotNull(){
		return $this->applyRuleToValues(function($val){ return is_null($val); }, func_get_args(), true);	
	}
	
	
	
	public function isEmpty(){
		return $this->applyRuleToValues(function($val){ return empty($val); }, func_get_args());
	}
	
	
	
	public function isNotEmpty(){
		return $this->applyRuleToValues(function($val){ return empty($val); }, func_get_args(), true);
	}
	
}

?>