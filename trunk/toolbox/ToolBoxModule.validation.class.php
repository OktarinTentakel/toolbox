<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleValidation contains methods to validate formats and ranges of values
 * according to standards and self-implemented rules.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage formats
 */
class ToolBoxModuleValidation extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Checks if the given value represents an unsigned integer. 
	 * 
	 * @param * $value the value to check, must be string-castable
	 * @param Boolean $signed defines if the expected value is signed or unsigned
	 * @return Boolean true/false
	 */
	public function isInteger($value, $signed = true){
		return preg_match("/^".($signed ? '\-?' : '')."[1-9][0-9]*$/", $value);
	}
	
	
	
	/**
	 * Checks if the given value can be interpreted as a boolean.
	 * Returns true if the value is a boolean string, additional to PHPs
	 * standard is_bool.
	 * 
	 * @param * $value the value to check, must be string-castable
	 * @param Boolean $stringValues defines if boolean values are also accepted as strings
	 * @return Boolean true/false
	 */
	public function isBoolean($value, $stringValues = false){
		return (is_bool($value) || ($stringValues && in_array($value, array('true', 'false'))));
	}
	
	
	
	/**
	 * Checks if the given value can be represents a valid URL.
	 * 
	 * @param * $value the value to check, must be string-castable
	 * @param Boolean $needsProtocol defines if an access-protocol has to be part of the URL
	 * @return Boolean true/false
	 */
	public function isUrl($value, $needsProtocol = true){
		$urlregex = $needsProtocol ? "^(https?|ftp)\:\/\/" : "^((https?|ftp)\:\/\/)?";
		$urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

		//$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*";  // http://x = allowed (ex. http://localhost, http://routerlogin)
		//$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+";  // http://x.x = minimum
		$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}";  // http://x.xx(x) = minimum

		$urlregex .= "(\:[0-9]{2,5})?";
		$urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
		$urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@\/&%=+\$_.-]*)?";
		$urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
		
		return preg_match('/'.$urlregex.'/i', "$value");
	}
	
	
	
	/**
	 * Checks if the given value represents a valid email-address.
	 * 
	 * @param * $value the value to check, must be string-castable
	 * @return Boolean true/false
	 */
	public function isEmail($value){
		if( !preg_match('/^[^@]{1,64}@[^@]{1,255}$/u', "$value") ){
			return false;
		}
			
		$email_array = explode('@', "$value");
		$local_array = explode('.', $email_array[0]);
			
		for( $i = 0; $i < sizeof($local_array); $i++ ){
			if( !preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/u", $local_array[$i]) ){
				return false;
			}
		}
			
		if( !preg_match('/^\[?[0-9\.]+\]?$/u', $email_array[1]) ){
			$domain_array = explode('.', $email_array[1]);
	
			if( count($domain_array) < 2 ){
				return false;
			}
	
			for( $i = 0; $i < sizeof($domain_array); $i++ ){
				if( !preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]{2,5}))$/u', $domain_array[$i]) ){
					return false;
				}
			}
		}
	
		return true;
	}
	
}

?>