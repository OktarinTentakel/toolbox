<?php 

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';
require_once 'ToolBoxModuleSingleton.absclass.php';



//--|CLASS----------

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
		
		if( isset($_COOKIE[$this->sessionName]) && isset($_SESSION['ToolBox::'.get_class()]) ){
			session_name($this->sessionName);
			session_start();
			
			$this->user = $_SESSION['ToolBox::'.get_class()]->getUser();
		} else {
			$this->logout();
		}
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	public function init(){}
	
	
	
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
	
	
	
	public function loggedIn(){
		return (isset($_SESSION['ToolBox::'.get_class()]));
	}
	
	
	
	public function getUser(){
		return (object)($this->user);
	}
}

?>