<?php 

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';
require_once 'ToolBoxModuleSingleton.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleAuthentication contains everything concerning authentication procedures for accessing web
 * resources and websites including helpers for maintaining security levels while browsing.
 * 
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage procedures
 */
class ToolBoxModuleAuthentication extends ToolBoxModule {
	
	const DEFAULT_SESSION_NAME = 'ToolBoxAuthenticationSession';
	
	const SINGLETON_AUTHENTICATOR = 'Authenticator';
	public static $SINGLETON_CLASSES = array(self::SINGLETON_AUTHENTICATOR);
	
	
	
	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
}



//--|NESTED-SINGLETON-[Authenticator]----------

/**
 * Authenticator provides the means to easily implement basic authentication on a website
 * using the classical way of session-based authentication, preferrably cookie-enabled.
 * 
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package singletons
 * @subpackage procedures
 */
class Authenticator extends ToolBoxModuleSingleton {

	const ARGUMENT_SESSION_NAME = 'SESSION_NAME';
	
	
	
	// ***
	public static $instance = null;
	
	private $sessionName = null;
	private $user = null;
	
	
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		if( isset($args[self::ARGUMENT_SESSION_NAME]) ){
			$this->sessionName = ''.$args[self::ARGUMENT_SESSION_NAME];
		} else {
			$this->sessionName = ToolBoxModuleAuthentication::DEFAULT_SESSION_NAME;
		}
		
		if( isset($_COOKIE[$this->sessionName]) ){
			session_name($this->sessionName);
			session_start();
			
			$this->user = isset($_SESSION['ToolBox::'.get_class()]) ? $_SESSION['ToolBox::'.get_class()]->getUser() : null;
		}
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	/**
	 * Standard initialisation method.
	 */
	public function init(){}
	
	
	
	/**
	 * Tries to login using a login name and a password based on a collection
	 * of present userdata. This method works with string-login-ids and
	 * md5-hashed passwords. Returns the success.
	 * 
	 * @param String $login the login name to try to login
	 * @param String $password the correspondig password as plain text
	 * @param Array $users collection of all possible user, that may login in the format array('login' => 'login', 'password' => 'password') or {login : 'login', password : 'passowrd'}
	 * @return Boolean true/false
	 */
	public function login($login, $password, Array $users){
		foreach($users as $user){
			if( is_array($user) ){
				if(
					($user['login'] == "$login")
					&& ($user['password'] == md5($password))
				){
					$this->user = $user;
					break;
				}
			} elseif( is_object($user) ){
				if(
					($user->login == "$login")
					&& ($user->password == md5($password))
				){
					$this->user = $user;
					break;
				}
			}
		}
		
		if( !is_null($this->user) ){
			if( session_id() == '' ){
				session_name($this->sessionName);
				session_start();
			}
			
			$_SESSION['ToolBox::'.get_class()] = self::$instance;
			
			return true;
		} else {
			return false;
		}
	}
	
	
	
	/**
	 * Tries to log out the currently logged in user of the session and destroys the session.
	 */
	public function logout(){
		$_SESSION = array();
		$this->user = null;
		
		if( isset($_COOKIE[session_name()]) ){
			setcookie(session_name(), session_id(), time()-42000, '/');
			unset($_COOKIE[session_name()]);
		}
		
		if( session_id() != '' ){
			session_destroy();
		}
	}
	
	
	
	/**
	 * Returns if a user is currently logged in.
	 * 
	 * @return Boolean true/false
	 */
	public function loggedIn(){
		return (isset($_SESSION['ToolBox::'.get_class()]));
	}
	
	
	
	/**
	 * Returns the current userdata-set, as discovered on login from the given userdata-collection.
	 * 
	 * @return Object|null the current userdata set
	 */
	public function getUser(){
		return (object)($this->user);
	}
}

?>