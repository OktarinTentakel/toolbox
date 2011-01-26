<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleObject extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function toAssocArray($object) {
		$return = array();
		
		if( is_array($object) ){
			foreach( $object as $key => $value ){
				$return[$key] = self::objectToArray($value);
			}
		} else {
			$var = get_object_vars($object);
			
			if( $var ){
				foreach( $var as $key => $value ){
					$return[$key] = ($key && ($value==null)) ? null : self::objectToArray($value);
				}
			}else{
				return "$object";
			}
		}
		return $return;
	}
	
}

?>