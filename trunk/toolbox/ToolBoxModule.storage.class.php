<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleStorage extends ToolBoxModule {
	const SINGLETON_SQLITECONNECTION = 'SqliteConnection';
	public static $SINGLETON_CLASSES = array(self::SINGLETON_SQLITECONNECTION);

	// ***
	public function __construct($moduleName, $addedArgs){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
}



//--|NESTED-SINGLETON-[SqliteConnection]----------

class SqliteConnection extends ToolBoxModuleSingleton {
	
	const DB_FILE = 'DB_FILE';
	
	// ***
	public static $instance = null;
	
	
	private $dbFile = null;
	private $db = null;
	private $queries = array();
	private $execQueries = array();
	
	protected function __construct(Array $args = null){
		parent::__construct($args);
		
		if( isset($args[self::DB_FILE]) ){
			$this->dbFile = ''.$args[self::DB_FILE];
		} else {
			$this->throwModuleException(__FUNCTION__.': no database-file given');
		}
	}
	// ***
	
	
	
	//--|GETTER----------
	
	public function getLastCreatedId(){
		$atomOp = false;
		
		if( !$this->isOpen ){
			$this->open();
			$atomOp = true;
		}
		
		$res = array_shift($this->db->query('SELECT last_insert_rowid() as lastid;')->fetchArray(SQLITE3_ASSOC));
		
		if( $atomOp ){
			$this->close();
		}
		
		return $res;
	}
	
	
	
	//--|QUESTIONS----------
	
	public function isOpen(){
		return !is_null($this->db);
	}
	
	
	
	//--|FUNCTIONALITY----------
	
	public function open(){
		$this->db = new SQLite3($this->dbFile);
	}
	
	
	
	public function close(){
		$this->db->close();
		$this->db = null;
	}
	
	
	
	public function query($query, $mode = SQLITE3_ASSOC){
		$res = array();
		$atomOp = false;
		
		if( !$this->isOpen() ){
			$this->open();
			$atomOp = true;
		}
		
		$rawRes = $this->db->query($query); 
		while( $row = $rawRes->fetchArray($mode) ){
			$res[] = $row;
		}
		
		if( $atomOp ){
			$this->close();
		}
		
		return $res;
	}
	
	
	
	public function addQuery($query){
		$this->queries[] = "$query";
	}
	
	
	
	public function queryAll($mode = SQLITE3_ASSOC){
		$res = array();
		
		if( !$this->isOpen() ){
			$this->open();
		}
		
		foreach( $this->queries as $query ){
			$rawRes = $this->db->query($query); 
			while( $row = $rawRes->fetchArray($mode) ){
				$res[$query][] = $row;
			}
		}
		
		$this->close();
		
		$this->queries = array();
		
		return $res;
	}
	
	
	
	public function exec($query){
		$atomOp = false;
		
		if( !$this->isOpen() ){
			$this->open();
			$atomOp = true;
		}
		
		$this->db->exec($query);
		
		if( $atomOp ){
			$this->close();
		}
	}
	
	
	
	public function addExecQuery($query){
		$this->execQueries[] = "$query";
	}
	
	
	
	public function execAll(){
		if( !$this->isOpen() ){
			$this->open();
		}
		
		foreach( $this->execQueries as $query ){
			$this->db->exec($query);
		}
		
		$this->close();
		
		$this->execQueries = array();
	}
	
}

?>