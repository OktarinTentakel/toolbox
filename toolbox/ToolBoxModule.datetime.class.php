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
	 * Converts a date string in a standard time format to a ISO-date-string.
	 * The string may be any PHP-parsable datetime-string.
	 * 
	 * @param String $dateString the date-string to convert
	 * @return String to ISO-formatted date-string
	 */
	public function miscToIsoDate($dateString){
		return ($dateString != '') ? date('Y-m-d', strtotime($dateString)) : '';
	}
	
	
	
	/**
	 * Converts a time string in a standard time format to a ISO-time-string.
	 * The string may be any PHP-parsable datetime-string.
	 *
	 * @param String $timeString the time-string to convert
	 * @return String to ISO-formatted time-string
	 */
	public function miscToIsoTime($timeString){
		return ($timeString != '') ? date('H:i:s', strtotime($timeString)) : '';
	}
	
	
	
	/**
	 * Converts a datetime string in a standard datetime format to a ISO-datetime-string.
	 * The string may be any PHP-parsable datetime-string.
	 *
	 * @param String $dateTimeString the datetime-string to convert
	 * @return String to ISO-formatted datetime-string
	 */
	public function miscToIsoDateTime($dateTimeString){
		return ($dateTimeString != '') ? date('Y-m-d H:i:s', strtotime($dateTimeString)) : '';
	}
	
}

?>