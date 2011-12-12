<?php

/**
 * If you want to use this module, include a .htaccess with these settings next to your index.php / main-php
 * 
 *	<IfModule mod_rewrite.c>
 *		RewriteEngine on
 *
 *		RewriteCond %{REQUEST_FILENAME} !-f
 *		RewriteCond %{REQUEST_FILENAME} !-d
 *
 *		RewriteRule ^(.*)$ index.php/$1 [L]
 *	</IfModule>
 **/

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';
require_once 'ToolBoxModuleSingleton.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleRouting contains methods and tools for dealing with URL-management and binding
 * request-configurations to application contexts.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage procedures
 */
class ToolBoxModuleRouting extends ToolBoxModule {
	
	const SINGLETON_ROUTER = 'Router';
	public static $SINGLETON_CLASSES = array(self::SINGLETON_ROUTER);
	
	
	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	//--|TOPLEVEL----------
	
	/**
	 * Redirects to a given URL and stops any further processing of the current script.
	 * 
	 * @param String $url the URL to redirect to
	 * @param Boolean $noCache tells the browser if the cache should be ignored for the redirect
	 */
	public function redirect($url, $noCache = false){
		if( $noCache ){
			header('Location: '.$url, true, 302);
		} else {
			header('Location: '.$url);
		}
		
		exit();
	}
	
}



//--|NESTED-SINGLETON-[Router]----------

/**
 * Router is an apache-mod_rewrite-based URL-router, which
 * parses the current URL (path without domain) with regular expressions and routes according to
 * the format to defined end points in the script.
 * 
 * These are some examples for the router usage with explanations:
 * 
 * # on no path -> require_once [r] on test.class.php, call indexFunction statically [s] on class test
 * ToolBox::get()->Router->addShortRule('', 'Test:[php/test.class.php]/indexFunction/[rs]');
 * 
 * # on /objecttest/123/abc -> instantiate a Test-object and call objectMethod on it, providing two parameters from the URL
 * # In this case the parameters are partly casted and expanded upon. The last array defines order and can add further params, not coming from the URL
 * ToolBox::get()->Router->addShortRule('objecttest/(\d+)/(\w+)', 'Test/objectMethod/a:integer/b/', array('$2', '$1', array('test', 'test')));
 * 
 * # on /objecttest2/123/abc -> take the present object $testObject and call objectMethod on it, providing two parameters from the URL and nothing else
 * ToolBox::get()->Router->addShortRule('objecttest2/(\d+)/(\w+)', '/objectMethod/a:integer/b/', null, $testObj);
 * 
 * # on /statictest/abc/123  -> statically [s] call staticFunction on class Test and provide two parameters from the URL and nothing else
 * ToolBox::get()->Router->addShortRule('statictest/(\w+)/(\d+)', 'Test/staticFunction/a/b:integer/[s]');
 * 
 * # on /globaltest/abc/123/def -> require_once [r] test.class.php and globally call the funtion globalFunction and provide three parameters from the URL
 * ToolBox::get()->Router->addShortRule('globaltest/(\w+)/(\w+)/(\d+)', ':[php/test.class.php]/globalFunction/a/b/c:integer/[r]');
 * 
 * # on /globaltest/abc -> call print_r with the parameter from the URL
 * ToolBox::get()->Router->addShortRule('globaltest/(\w+)', '/print_r/a/');
 * 
 * # on /mixedGetMethod/abc/def -> call mixedGetMethod on present object $testObj with two parameters not from the URL and two parameters
 * # from the URL not used a parameters, but set as GET-parameters [g] before calling the method
 * ToolBox::get()->Router->addShortRule('mixedgettest/(\w+)/(\w+)', '/mixedGetMethod/a/b/[g]', array('aa', 'bb', '$1', '$2'), $testObj);
 * 
 * # on no rule applying -> require_once test.class.php [r] and call statically [s] call fourOfourFunction on class Test 
 * ToolBox::get()->Router->addShortRule(404, 'Test:[php/test.class.php]/fourOfourFunction/[rs]');
 * 
 * # parse URL and try to apply a rule to it
 * ToolBox::get()->Router->exec();
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package singletons
 * @subpackage procedures
 */
class Router extends ToolBoxModuleSingleton {

	// ***
	public static $instance = null;
	
	private $currentRoute = null;
	private $rules = null;
	private $rule404 = null;
	
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		$this->currentRoute =  
			urldecode(
				preg_replace(
					'/^'.$this->escapeStringForRegExp($_SERVER['SCRIPT_NAME']).'(.*)$/',
					'$1', $_SERVER['PHP_SELF']
				)
			)
		;
		
		$this->rules = array();
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	/**
	 * Masks a string for use in regular expression, to remove implicit meanings of
	 * special regex-characters.
	 * 
	 * @param String $string the string to mask
	 * @param Boolean $onlySlashes defines if only slashes should be masked and special characters should be left intact
	 * @return String the masked string
	 */
	private function escapeStringForRegExp($string, $onlySlashes = false){
		if( !$onlySlashes ){
			$string = preg_quote($string);
		}
		
		return str_replace('/', '\/', $string);
	}
	
	
	
	/**
	 * Validates the string format of a short-rule-string.
	 * 
	 * @param String $shortRule the short-rule-string to check
	 * @return Boolean true/false
	 */
	private function checkShortSyntax($shortRule){
		return preg_match(
			'/^(([A-Z][a-zA-Z0-9]+)?(\:\[.+\])?)?\/.+(\/.+(\:.+)?)*\/(\[[rsg]+\])?$/',
			$shortRule
		);
	}
	
	
	
	/**
	 * Throws a standard Exception for a syntax error in a short-rule-string.
	 * 
	 * @throws Exception
	 */
	private function throwShortSyntaxErrorException(){
		throw new Exception('ToolBoxException | shortrule-syntax-error');
	}
	
	
	
	/**
	 * Adds a rule to the ruleset.
	 * 
	 * @param String $regExp the regexp to match against the current URL-path
	 * @param String $functionName function name to call on a match
	 * @param Array $functionArguments function arguments to call the function with
	 * @param String $className the name of the class to call the function statically on or to instantiate an object of to call the method on
	 * @param String $include a string to use as a include value before the execution, to dynamically include code on rule execution
	 * @param Object $targetObject an already existent object to call the method above on
	 * @param Boolean $useRequireOnce defines if require_once should be used instead of include in case of a dynamic include
	 * @param Boolean $callStatic defines if the function should be called statically on the class instead on an object
	 * @param Boolean $createArgsAsGet defines if named parameters to the method should be transformed into get-parameters
	 */
	public function addRule(
		$regExp,
		$functionName,
		Array $functionArguments = null,
		$className = null,
		$include = null,
		&$targetObject = null,
		$useRequireOnce = true,
		$callStatic = false,
		$createArgsAsGet = false
	){
		$rule = new StdClass();
		$rule->rex = '/^\/?'.$this->escapeStringForRegExp("$regExp", true).'\/?$/';
		$rule->class = !is_null($className) ? "$className" : null;
		$rule->method = "$functionName";
		$rule->args = $functionArguments;
		$rule->include = !is_null($include) ? "$include" : null;
		$rule->target = &$targetObject;
		$rule->require = $useRequireOnce ? true : false;
		$rule->static = $callStatic ? true : false;
		$rule->asget = $createArgsAsGet ? true : false; 
		
		if( $regExp !== 404 ){
			$this->rules[] = $rule;
		} else {
			$this->rule404 = $rule;
		}
	}
	
	
	
	/**
	 * Adds a new rule to the ruleset, by using a special short syntax to circumvent
	 * the parameter-heavy call of addRule.
	 * 
	 * rough syntax:
	 * Class:[includestring]/method/arg1:type/arg2:type/.../[rsg]
	 * /method/arg/ 
	 * 
	 * for argumentMap (if not set only url-args in that order, dollar marks URL-params):
	 * array('asd', 11, '$1', 'ddd', '$2')
	 * 
	 * @param String $regExp the regexp to match against the current URL-path
	 * @param String $shortRule the short-rule-string to parse to a call rule
	 * @param Array $argumentMap list of parameters to call the function with, including URL-param-placeholders
	 * @param Object $targetObject an existing object to call the method on, instead of calling it statically or on a new object
	 * @throws Exception on invalid short-syntax
	 */
	public function addShortRule($regExp, $shortRule, Array $argumentMap = null, &$targetObject = null){
		if( $this->checkShortSyntax($shortRule) ){
			$includeString = preg_match('/\:\[(.+\.php)\]/', $shortRule, $includeHits) ? $includeHits[1] : null;
			$shortRule = !is_null($includeString) ? preg_replace('/\:\[.+\.php\]/', '', $shortRule) : $shortRule;
			
			$rulePieces = explode('/', $shortRule);
			$className = ($rulePieces[0] != '') ? $rulePieces[0] : null; 
			$functionName = $rulePieces[1];
			
			if( (count($rulePieces) > 3) || !is_null($argumentMap) ){
				$functionArguments = array();
				
				if( is_null($argumentMap) || (count($argumentMap) == 0) ){
					for($i = 2; $i < count($rulePieces)-1; $i++){
						$argumentMap[] = '$'.($i-1);
					}
				}
				
				for($i = 0; $i < count($argumentMap); $i++){
					if( $argumentMap[$i][0] == '$' ){
						$argPieces = null;
					
						if( preg_match('/^\$[1-9]+$/', $argumentMap[$i]) ){
							$argIndex = intval(str_replace('$', '', $argumentMap[$i]));
							if( isset($rulePieces[$argIndex+1]) && ($argIndex < count($rulePieces)) ){
								$argPieces = explode(':', $rulePieces[$argIndex+1]); 
							}
						} else {
							$argName = str_replace('$', '', $argumentMap[$i]);
							foreach( $rulePieces as $rulePiece ){
								if( strncmp($rulePiece, $argName, strlen($argName)) == 0 ){
									$argPieces = explode(':', $rulePiece); 
									break;
								}
							}
						}
						
						if( !is_null($argPieces) ){
							$arg = new StdClass();
							$arg->name = $argPieces[0];
							$arg->val = $argumentMap[$i];
							$arg->type = (count($argPieces) > 1) ? $argPieces[1] : 'string'; 
							
							$functionArguments[] = $arg;
						}
					} else {
						$arg = new StdClass();
						$arg->name = null;
						$arg->val = $argumentMap[$i];
						$arg->type = null; 
						
						$functionArguments[] = $arg;
					}
				}
			} else {
				$functionArguments = null;
			}
			
			$modificators = $rulePieces[count($rulePieces)-1];
			$useRequireOnce = (strpos($modificators, 'r') !== false);
			$callStatic = (strpos($modificators, 's') !== false);
			$createArgsAsGet = (strpos($modificators, 'g') !== false);
			
			$this->addRule($regExp, $functionName, $functionArguments, $className, $includeString, $targetObject, $useRequireOnce, $callStatic, $createArgsAsGet);
		} else {
			$this->throwShortSyntaxErrorException();
		}
	}
	
	
	
	/**
	 * Executes the router's rule stack. And executes the first rule that applies.
	 */
	public function exec(){
		$fourOfour = true;
		
		foreach( $this->rules as $rule ){
			if( preg_match($rule->rex, $this->currentRoute, $argHits) ){
				$this->executeRule($rule, $argHits);
				$fourOfour = false;
				break;
			}
		}
		
		if( $fourOfour && !is_null($this->rule404) ){
			$this->executeRule($this->rule404);
		}
	}
	
	
	
	/**
	 * Executes a rule, by calling the set method in the specified way.
	 * 
	 * @param StdClass $rule the rule object to execute
	 * @param array $argHits the parsed parameter value hits from the preg_match-call on the URL
	 */
	private function executeRule(StdClass $rule, Array $argHits = null){
		if( !is_null($rule->include) ){
			if( !$rule->require ){
				include $rule->include;
			} else {
				require_once $rule->include;
			}
		}
		
		if( !is_null($rule->class) && is_null($rule->target) ){
			$routeTarget = new $rule->class();
		} elseif( !is_null($rule->target) ){
			$routeTarget = &$rule->target;
		} else {
			$routeTarget = null;
		}
		
		$methodArgs = array();
		if( !is_null($rule->args) ){
			foreach( $rule->args as $arg ){
				if( is_null($arg->name) ){
					$methodArgs[] = $arg->val;
				} else {
						$argValue = str_replace('$', '', $arg->val);
						$argKey = preg_match('/^[1-9]+$/', $arg->val) ? intval($argValue) : $argValue;
						$value = $argHits[$argKey];
						settype($value, $arg->type);
						
						if( !$rule->asget ){
							$methodArgs[] = $value;
						} else {
							$_GET[$arg->name] = $value;
						}
				}
			}
		}
			
		if( !is_null($routeTarget) ){
			if( !$rule->static ){
				call_user_func_array(array($routeTarget, $rule->method), $methodArgs);
			} else {
				call_user_func_array(array($rule->class, $rule->method), $methodArgs);
			}
		} else {
			call_user_func_array($rule->method, $methodArgs);
		}
	}
}

?>