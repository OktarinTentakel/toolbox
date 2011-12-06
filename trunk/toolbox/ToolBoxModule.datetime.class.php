<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleDateTime contains helper methods for dealing time and date tasks.
 * PHP5 already has a strong and flexible selection of time-handling rountines, but especially
 * conversions for web and DBs are lacking. This among other things is meant to be solved by this module.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage formats
 */
class ToolBoxModuleDatetime extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Converts a date string in a standard time format to an Standard-date-string.
	 * The default output is the simple ISO-format (2011-01-31).
	 * The string may be any PHP-parsable datetime-string.
	 * 
	 * @param String $dateString the date-string to convert
	 * @param String $specialParseFormat optinonal special format to use for parsing, use DateTime-constants if possible
	 * @param String $outputFormat optinonal special format to use for parsing, use DateTime-constants if possible
	 * @return String formatted date-string
	 */
	public function miscToStandardDate($dateString, $specialParseFormat = null, $outputFormat = 'Y-m-d'){
		if( is_null($specialParseFormat) ){
			$time = strtotime(''.$dateString);
			return $time ? date(''.$outputFormat, $time) : '';
		} else {
			$dateTime = DateTime::createFromFormat($specialParseFormat, ''.$dateString);
			return $dateTime ? $dateTime->format(''.$outputFormat) : '';
		}
	}
	
	
	
	/**
	 * Converts a time string in a standard time format to an Standard-time-string.
	 * The default output is the simple ISO-format (16:01:02).
	 * The string may be any PHP-parsable datetime-string.
	 *
	 * @param String $timeString the time-string to convert
	 * @param String $specialParseFormat optinonal special format to use for parsing, use DateTime-constants if possible
	 * @return String formatted time-string
	 */
	public function miscToStandardTime($timeString, $specialParseFormat = null, $outputFormat = 'H:i:s'){
		if( is_null($specialParseFormat) ){
			$time = strtotime(''.$timeString);
			return $time ? date(''.$outputFormat, $time) : '';
		} else {
			$dateTime = DateTime::createFromFormat($specialParseFormat, ''.$timeString);
			return $dateTime ? $dateTime->format(''.$outputFormat) : '';
		}
	}
	
	
	
	/**
	 * Converts a datetime string in a standard datetime format to an Standard-datetime-string.
	 * The default output is the simple ISO-format (2011-01-31 16:01:02).
	 * The string may be any PHP-parsable datetime-string.
	 *
	 * @param String $dateTimeString the datetime-string to convert
	 * @param String $specialParseFormat optinonal special format to use for parsing, use DateTime-constants if possible
	 * @return String formatted datetime-string
	 */
	public function miscToStandardDateTime($dateTimeString, $specialParseFormat = null, $outputFormat = 'Y-m-d H:i:s'){
		if( is_null($specialParseFormat) ){
			$time = strtotime(''.$dateTimeString);
			return $time ? date(''.$outputFormat, $time) : '';
		} else {
			$dateTime = DateTime::createFromFormat($specialParseFormat, ''.$dateTimeString);
			return $dateTime ? $dateTime->format(''.$outputFormat) : '';
		}
	}
	
}

?>