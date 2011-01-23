<?php 

//--|INCLUDES----------

require_once 'toolboxmodule.absclass.php';



//--|CLASS----------

class ToolBoxModuleAuthentification extends ToolBoxModule {
	public static $SINGLETON_CLASSES = array('Authentificator');
	
	
	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
}



class Authentificator {
	const SESSION_NAME = 'darkwood2admin';
	
	// ***
	private static $instance = null;
	
	
	
	private $user = null;
	
	
	
	private function __construct(){
		if( isset($_COOKIE[self::SESSION_NAME]) ){
			session_name(self::SESSION_NAME);
			session_start();
			
			$this->user = $_SESSION['user'];
		}
	}
	
	
	
	public static function get(){
		if( is_null(self::$instance) ){
			self::$instance = new Authentificator();
		}
			
		return self::$instance;
	}
	// ***
	
	
	
	//--|FUNCTIONALITY----------
	
	public function init(){}
	
	
	
	public function login($login, $pass){
		$this->user = UserQuery::create()
			->filterByLogin($login)
			->filterByPassword(md5($pass))
			->findOne()
		; 
		
		if( !is_null($this->user) ){
			if( session_id() == '' ){
				session_name(self::SESSION_NAME);
				session_start();
			}
			
			$this->user->setLastLogin(date('Y-m-d H:i:s'));
			$this->user->save();
			
			$_SESSION['logged_in'] = true;
			$_SESSION['user'] = $this->user;
			
			return true;
		} else {
			return false;
		}
	}
	
	
	
	public function logout(){
		$_SESSION = array();
		
		if( isset($_COOKIE[session_name()]) ){
			setcookie(session_name(), '', time()-42000, '/');
		}
		
		session_destroy();
	}
	
	
	
	public function loggedIn(){
		return (isset($_SESSION['logged_in']) && $_SESSION['logged_in']);
	}
	
	
	
	public function getUser(){
		return $this->user;
	}
}

?>