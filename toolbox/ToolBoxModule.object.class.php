<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleObject offers helpers to convert and work with plain objects, as well as tricks with
 * default properties and methods of other objects. 
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage datatypes
 */
class ToolBoxModuleObject extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Transforms a plain object into an associative array, by grabbing available properties
	 * and setting up those as named key/value-entries. Can also recursively include assiciative arrays, even
	 * if the first object is an array itself, containing objects and other data.
	 * 
	 * @param Object $object the object to transform
	 * @return Array the transformed array 
	 */
	public function toAssocArray($object) {
		$return = array();
		
		if( is_array($object) ){
			foreach( $object as $key => $value ){
				$return[$key] = self::toAssocArray($value);
			}
		} else {
			if( is_object($object) ){
				$var = get_object_vars($object);
				
				if( !is_null($var) ){
					foreach( $var as $key => $value ){
						$return[$key] = ($key && ($value==null)) ? null : self::toAssocArray($value);
					}
				}
			} else {
				if(
					is_string($object)
					|| is_numeric($object)
					|| is_bool($object)
					|| is_null($object)
				){
					return $object;
				} else {
					return preg_replace('/\s/', '', print_r($object, true));
				}
			}
		}
		
		return $return;
	}
	
}

?>