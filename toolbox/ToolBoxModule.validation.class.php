<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleValidation extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function isNumber($value) {
		return preg_match("/^[0-9]+$/", $value);
	}
	
	
	
	public function isUrl($value){
		// Zugriffsart
		$urlregex = "^(https?|ftp)\:\/\/";

		// optionale Angaben zu User und Passwort
		$urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

		// Hostname oder IP-Angabe
		//$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*";  // http://x = allowed (ex. http://localhost, http://routerlogin)
		//$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+";  // http://x.x = minimum
		$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}";  // http://x.xx(x) = minimum
		//use only one of the above

		// optionale Portangabe
		$urlregex .= "(\:[0-9]{2,5})?";
		// optionale Pfadangabe
		$urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
		// optionaler GET-Query
		$urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@\/&%=+\$_.-]*)?";
		// optionaler Seitenanker
		$urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
		
		return preg_match('/'.$urlregex.'/i', $value);
	}
	
}

?>