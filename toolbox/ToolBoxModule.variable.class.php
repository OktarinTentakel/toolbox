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
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Takes a function and applys it to an array of given values.
	 * The function has to return a boolean-castable array, to actually work.
	 * In theory, this method is made to validate values and return the end result, but practically
	 * you could also use this to modify values, by only returning true and ignoring the end result.
	 * 
	 * @param Closure $rule function to apply to all given values
	 * @param Array $values all values to apply the given rule ro
	 * @param Boolean $negation inverts the method's logic (rules have to fail)
	 * @return Boolean true/false
	 */
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
	
	
	
	/**
	 * Checks if all given values are null.
	 * 
	 * @param * .. values to check
	 * @return Boolean true/false
	 */
	public function isNull(){
		return $this->applyRuleToValues(function($val){ return is_null($val); }, func_get_args());
	}
	
	
	
	/**
	 * Checks if none of the given values are null.
	 * 
	 * @param * .. values to check
	 * @return Boolean true/false
	 */
	public function isNotNull(){
		return $this->applyRuleToValues(function($val){ return is_null($val); }, func_get_args(), true);	
	}
	
	
	
	/**
	 * Checks if all given values are empty.
	 *
	 * @param * .. values to check
	 * @return Boolean true/false
	 */
	public function isEmpty(){
		return $this->applyRuleToValues(function($val){ return empty($val); }, func_get_args());
	}
	
	
	
	/**
	 * Checks if none of the given values are empty.
	 *
	 * @param * .. values to check
	 * @return Boolean true/false
	 */
	public function isNotEmpty(){
		return $this->applyRuleToValues(function($val){ return empty($val); }, func_get_args(), true);
	}
	
}

?>