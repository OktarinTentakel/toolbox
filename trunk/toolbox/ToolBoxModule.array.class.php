<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleArray contains helper methods for dealing with normal and associative arrays.
 * While the standard library of array-functionality in PHP5 is quite strong already, there are
 * many special use cases, that are covered badly or overcomplicated. This module is supposed to streamline
 * array usage.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage datatypes
 */
class ToolBoxModuleArray extends ToolBoxModule {

	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Recursively concatenates all kinds of parameters into a non-associative array.
	 * Takes n parameters and sub-processes arrays, by putting the values one by one into a new one.
	 * This is a convenience method for mixed parameter-sets, fire and forget style, if you want to combine
	 * several arrays or large sets, then use array_merge, since it's a lot faster.
	 * 
	 * @return Array the result array, with all values in proper order
	 */
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
	
	
	
	/**
	 * Transforms an associative array into a php standard object, with named attributes according to the associative keys.
	 * The difference to normal object casting is, that only associative arrays will be processed, non string keys will be skipped.
	 * 
	 * @param Array $array the associative base array to use
	 * @return Object the transformed object
	 */
	public function assocToObject(Array $array){
		$res = new StdClass();
		
		foreach( $array as $key => $value ){
			if( is_string($key) ){
				$res->$key = $value;
			}
		}
		
		return $res;
	}
	
}

?>