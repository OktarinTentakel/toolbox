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

class ToolBoxModuleRouting extends ToolBoxModule {
	const SINGLETON_ROUTER = 'Router';
	public static $SINGLETON_CLASSES = array(self::SINGLETON_ROUTER);
	
	
	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
	
	
	public function redirect($url){
		header('Location: '.$url);
	}
	
}



//--|NESTED-SINGLETON-[Router]----------

class Router extends ToolBoxModuleSingleton {

	// ***
	public static $instance = null;
	
	private $currentRoute = null;
	private $rules = null;
	private $rule404 = null;
	
	
	protected function __construct(){
		parent::__construct();
		
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
	
	private function escapeStringForRegExp($string, $onlySlashes = false){
		if( !$onlySlashes ){
			$string = preg_quote($string);
		}
		
		return str_replace('/', '\/', $string);
	}
	
	
	
	private function checkShortSyntax($shortRule){
		return preg_match(
			'/^(([A-Z][a-zA-Z0-9]+)?(\:\[.+\])?)?\/.+(\/.+(\:.+)?)*\/(\[[rsg]+\])?$/',
			$shortRule
		);
	}
	
	
	
	private function throwShortSyntaxErrorException(){
		throw new Exception('ToolBoxException | shortrule-syntax-error');
	}
	
	
	
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
	 * Syntax:
	 * Class:includestring/method/arg1:type/arg2:type/.../[rs]
	 * /method/arg/ 
	 * 
	 * for argumentMap (if not set only url-args in that order):
	 * array('asd', 11, '$1', 'ddd', '$2')
	 */
	public function addShortRule($regExp, $shortRule, Array $argumentMap = null, &$targetObject = null){
		if( $this->checkShortSyntax($shortRule) ){
			$includeString = preg_match('/\:\[(.+\.php)\]/', $shortRule, $includeHits) ? $includeHits[1] : null;
			$shortRule = !is_null($includeString) ? preg_replace('/\:\[.+\.php\]/', '', $shortRule) : $shortRule;
			
			$rulePieces = explode('/', $shortRule);
			$className = ($rulePieces[0] != '') ? $rulePieces[0] : null; 
			$functionName = $rulePieces[1];
			
			if( count($rulePieces) > 3 ){
				$functionArguments = array();
				
				if( is_null($argumentMap) || (count($argumentMap) == 0) ){
					for($i = 2; $i < count($rulePieces)-1; $i++){
						$argumentMap[] = '$'.($i-1);
					}
				}
				
				for($i = 0; $i < count($argumentMap); $i++){
					if( $argumentMap[$i][0] == '$' ){
						$argIndex = intval(str_replace('$', '', $argumentMap[$i]));
						if( isset($rulePieces[$argIndex+1]) && ($argIndex < count($rulePieces)) ){
							$argPieces = explode(':', $rulePieces[$argIndex+1]); 
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
						$value = $argHits[intval(str_replace('$', '', $arg->val))];
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