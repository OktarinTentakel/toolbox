<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleJavaScript offers method to interact with JavaScript from within PHP.
 * There are certain cases, when server-generated data must be delegated to clients. The methods of this module should ease
 * those problems.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage client
 */
class ToolBoxModuleJavaScript extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Takes a standard PHP-Array a returns the contents as a string usable as a JavaScript-Array, if printed
	 * into a template for example.
	 * Works recursively on arrays, but can only transform ordinal types and arrays, no objects and such.
	 * 
	 * @param Array $array the array to transform
	 * @return String string representation of a JavaScript-array of the given PHP-array
	 */
	public function arrayToJsArrayString(Array $array){
		$res = '[';
		
		foreach( $array as $entry ){
			if( is_array($entry) ){
				$res .= $this->arrayToJsArrayString($entry).', ';
			} elseif( is_string($entry) ){
				$res .= "'$entry', ";
			} elseif( is_numeric($entry) ) {
				$res .= "$entry, ";
			} elseif( is_bool($entry) ){
				$res .= ($entry ? 'true' : 'false').', ';
			} elseif( is_null($entry) ){
				$res .= 'null, ';
			} else {
				$res .= "'".preg_replace('/\s/', '', print_r($entry, true))."', ";
			}
		}
		
		return mb_substr($res, 0, mb_strlen($res)-2).']';
	}
	
}

?>