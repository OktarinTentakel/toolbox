<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleGeneration contains helper methods for creating standard formats of data, used for
 * cases like generation of unique ids and other markers. 
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage formats
 */
class ToolBoxModuleGeneration extends ToolBoxModule {

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Returns an md5-Id with minimal risk of collision based on uniqid.
	 * 
	 * @param String $seed string to prefix the hashed uniqid with, useful for clearly dividing parallel id-generation
	 * @return String md5-string with random id
	 */
	public function uniqueMd5Id($seed = null){
		return md5(uniqid(is_null($seed) ? ''.mt_rand() : ''.$seed, true));
	}
	
	
	
	/**
	 * Returns an md5-id with minimal risk of collision based on microtime and rand.
	 * This method is faster than using uniqid and should be used if large numbers of ids have to be generated
	 * quickly in a single process. Is not as prone against collision as the uniqid way, but sufficient for all but extreme cases.
	 * 
	 * @return String md5-string with random id
	 */
	public function microRandMd5Id(){
		return md5(''.microtime().mt_rand());
	}
	
}

?>