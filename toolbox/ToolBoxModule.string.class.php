<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleString offers tools for the work with and manipulation of strings.
 * Besides general tools, this module offers auto-selection for mb_string-methods based
 * on utf-8 settings and encoding helpers especially to deal with utf-8.
 * 
 * This module absolutely _needs_ mb_string to work in basic ways. Do always install that
 * extension. Without it, string-manipulation is useless in PHP anyways. :P
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage datatypes
 */
class ToolBoxModuleString extends ToolBoxModule {

	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|MAGIC----------
	
	/**
	 * Magic method for unknow method calls.
	 * In this case used to capture calls for standard string calls, which can either be mb_string-methods
	 * or normal string calls, which will then be decided automatically based upon encoding settings and
	 * mb_string-availability. So, for example, if strlen is called on this module it will be decided automatically
	 * if to use exactly that method or whether to use mb_strlen.
	 * 
	 * @param String $name name of the method to call
	 * @param Array $args arguments for the method to call 
	 */
	public function __call($name, $args){
		if( strpos($name, 'auto_') === 0 ){
			return $this->autoSelectMethod(substr($name, 5), $args);
		}
	}
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Masks all regex-characters to use a string as part of a regex in its raw form without interpretation.
	 * This is an expansion of PHPs preg_quote, since that method doesn't mask slashes correctly.
	 * 
	 * @param String $string the string to mask
	 * @return String the masked string for in-regex-use
	 */
	public function escapeForRegExp($string){
		return str_replace(array('/'), array('\/'), preg_quote($string));
	}
	
	
	
	/**
	 * Prepares a rudimentary utf-8-environment.
	 */
	public function setUtf8Environment(){
		ini_set('default_charset', 'UTF-8');
		ini_set('mbstring.internal_encoding', 'UTF-8');
		ini_set('mbstring.http_output', 'UTF-8');
	}
	
	
	
	/**
	 * Checks if a rudimentary utf-8-environment is set.
	 * 
	 * @return Boolean true / false
	 */
	public function utf8EnvironmentSet(){
		return (
			(ini_get('default_charset') == 'UTF-8')
			&& (ini_get('mbstring.internal_encoding') == 'UTF-8')
			&& (ini_get('mbstring.http_output') == 'UTF-8')
		);
	}
	
	
	
	/**
	 * Automatically selects a string-method based on encoding-settings and mb-availability
	 * and executes that method, if existent, on the given string and returns the result.
	 * 
	 * @param String $name method-name to call
	 * @param Array $args arguments of the method
	 */
	public function autoSelectMethod($name, Array $args = array()){
		$methodName = ($this->utf8EnvironmentSet() ? 'mb_' : '').$name;
		
		if( function_exists($methodName) ){
			return call_user_func_array($methodName, $args);
		} elseif( method_exists($this, $methodName) ){
			return call_user_func_array(array($this, $methodName), $args);
		} else {
			return call_user_func_array($name, $args);
		}
	}
	
	
	
	/**
	 * Takes a string and replaces all utf-8-codes, in the form of \u1234,
	 * with the appropriate characters. This method is ripped from the zend
	 * framework, since there is absolutely no core method for this.
	 * 
	 * This method doesn't deal with mixed strings, that contain codes as well as
	 * unicode-characters. Unicode-characters in the string will break the processing.
	 * 
	 * @param String $chrs string to replace all code occurences in
	 * @return String string with replaced codes
	 */
	public function decodeUnicodeCodes($chrs){
		$delim = substr($chrs, 0, 1);
		$utf8 = '';
		$strlen_chrs = strlen($chrs);

		for($i = 0; $i < $strlen_chrs; $i++) {
			$substr_chrs_c_2 = substr($chrs, $i, 2);
			$ord_chrs_c = ord($chrs[$i]);

			switch( true ){
				case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $i, 6)):
					$utf16 = chr(hexdec(substr($chrs, ($i + 2), 2)))
							.chr(hexdec(substr($chrs, ($i + 4), 2)));
					$utf8 .= mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
					$i += 5;
				break;
				
				case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
					$utf8 .= $chrs{$i};
				break;
				
				case ($ord_chrs_c & 0xE0) == 0xC0:
					$utf8 .= substr($chrs, $i, 2);
					++$i;
				break;
				
				case ($ord_chrs_c & 0xF0) == 0xE0:
					$utf8 .= substr($chrs, $i, 3);
					$i += 2;
				break;
				
				case ($ord_chrs_c & 0xF8) == 0xF0:
					$utf8 .= substr($chrs, $i, 4);
					$i += 3;	
				break;
				
				case ($ord_chrs_c & 0xFC) == 0xF8:
					$utf8 .= substr($chrs, $i, 5);
					$i += 4;
				break;
				
				case ($ord_chrs_c & 0xFE) == 0xFC:
					$utf8 .= substr($chrs, $i, 6);
					$i += 5;
				break;
			}
		}

		return $utf8;
	}
	
	
	
	/**
	 * Adds strrev to the mb-namespace for this module's auto-method-selection, since
	 * this method is missing from mb so far.
	 * 
	 * @param String $str the string to reverse
	 * @param String $encoding the encoding to use
	 * @return String the reversed string
	 */
	public function mb_strrev($str, $encoding='UTF-8'){
		return mb_convert_encoding( strrev(mb_convert_encoding($str, 'UTF-16BE', $encoding)), $encoding, 'UTF-16LE');
	}
	
	
	
	/**
	 * Parses a string containing a "distinct-information"-format.
	 * Distinct information is a set of key/value-pairs (key:value) seperated by a newline
	 * or a "..:NL:.."-delimiter. The usage of this is normally to offer an expandable
	 * data-textfield of optional data to add random information to database-entries.
	 * This is more or less a cheap technique to add structure-irrelevant data in a document
	 * style to relational databases, without having to model each obscure possiblity, while keeping
	 * a very simple syntax for non-programmer editors in the backend. This proved to be a _very_
	 * efficient and convienient way to keep db-complexity managable for special and temporary purposes.
	 * 
	 * Example:
	 * special_display_name:fluffykitten\n
	 * tooltip_text:lorem ipsum dolor sit amet
	 * 
	 * @param String $string the string to parse
	 * @param String $separator the seperator between key and value
	 * @return StdClass the parsed string as a plain object, with each key as a property having the appropriate value
	 */
	public function parseDbDistinctInformation($string, $separator=':'){
		$res = new StdClass();
		
		$string = preg_replace('/\r\n|\r[^\n]|[^\r]\n/u', '..:NL:..', "$string");
		$pieces = explode('..:NL:..', $string);
		
		foreach( $pieces as $keyValue ){
			$keyValue = explode($separator, $keyValue, 2);
			if( count($keyValue) > 1 ){
				$res->{trim($keyValue[0])} = trim($keyValue[1]);
			}
		}
		
		return $res;
	}
	
	
	
	/**
	 * Truncates a string at a given character count and adds a suffix to the end.
	 * 
	 * @param String $string the string to truncate
	 * @param uint $charCount the amount of characters after which to truncate
	 * @param String $suffix the suffix to add to the end of the truncated string
	 * @return String the truncated string
	 */
	public function truncate($string, $charCount = 80, $suffix = '...'){
		$truncString = $this->auto_substr("$string", 0, $charCount); 
		return ($this->auto_strlen($string) != $this->auto_strlen($truncString)) ? $truncString.$suffix : $truncString;
	}
	
	
	
	/**
	 * Checks if a string starts with a certain substring.
	 * 
	 * @param String $target string to check within
	 * @param String $search string to use as substring
	 * @return Boolean true / false
	 */
	public function startsWith($target, $search){
		return (strncmp("$target", "$search", $this->auto_strlen($search)) == 0);
	}
	
	
	
	/**
	 * Checks if a string ends with a certain substring.
	 *
	 * @param String $target string to check within
	 * @param String $search string to use as substring
	 * @return Boolean true / false
	 */
	public function endsWith($target, $search){
		return (strncmp($this->auto_strrev($target), $this->auto_strrev($search), $this->auto_strlen($search)) == 0);
	}
	
	
	
	/**
	 * Splits a string at each captial character.
	 * This method only recognizes A-Z as captial characters.
	 * 
	 * @param String $string the string to split up
	 * @param String $splitChar the string to use as the splitting character(s)
	 * @return String the original string split by caps
	 */
	public function splitByCaps($string, $splitChar = ' '){
		return preg_replace('/([^A-Z])([A-Z])/', '$1'.$splitChar.'$2', $string);
	}
	
}

?>