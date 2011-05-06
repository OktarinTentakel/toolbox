<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

class ToolBoxModuleStorage extends ToolBoxModule {
	const SINGLETON_SQLITECONNECTION = 'SqliteConnection';
	const SINGLETON_SOLRLUKEHELPER = 'SolrLukeHelper';
	public static $SINGLETON_CLASSES = array(self::SINGLETON_SQLITECONNECTION, self::SINGLETON_SOLRLUKECONNECTION);

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
			$this->throwMissingSingletonDataException(self::DB_FILE);
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



//--|NESTED-SINGLETON-[SolrLukeHelper]----------

class SolrLukeHelper extends ToolBoxModuleSingleton {
	
	const SOLR_ADMIN_URL = 'SOLR_ADMIN_URL';
	const DEFAULT_SOLR_ADMIN_URL = 'http://%SERVER_NAME%:8080/solr/admin/';
	const OPTIMIZE_THRESHOLD = 'OPTIMIZE_THRESHOLD';
	const DEFAULT_OPTIMIZE_THRESHOLD = 10;
	const INDEX_XPATH = '/response/lst[@name="index"]/';
	
	// ***
	private static $instance;
	
	private $indexStats = null;
	private $solrAdminUrl = null;
	private $optimizeThreshold = null;
	
	protected function __construct(Array $args = null){
		if( isset($args[self::SOLR_ADMIN_URL]) ){
			$this->solrAdminUrl = ''.$args[self::SOLR_ADMIN_URL];
		} else {
			$this->solrAdminUrl = str_replace('%SERVER_NAME%', $_SERVER['SERVER_NAME'], self::DEFAULT_SOLR_ADMIN_URL);
		}
		
		if( isset($args[self::OPTIMIZE_THRESHOLD]) ){
			$this->optimizeThreshold = intval($args[self::OPTIMIZE_THRESHOLD]);
		} else {
			$this->optimizeThreshold = self::DEFAULT_OPTIMIZE_THRESHOLD;
		}
		
		$xml = simplexml_load_file($this->solrAdminUrl.'luke?fl=*');
		
 		$this->indexStats = new StdClass();
 		$this->indexStats->numDocs = $this->extractValue($xml->xpath(self::INDEX_XPATH.'*[@name="numDocs"]'));
 		$this->indexStats->maxDoc = $this->extractValue($xml->xpath(self::INDEX_XPATH.'*[@name="maxDoc"]'));
 		$this->indexStats->optimized = $this->extractValue($xml->xpath(self::INDEX_XPATH.'*[@name="optimized"]'));
 		$this->indexStats->current = $this->extractValue($xml->xpath(self::INDEX_XPATH.'*[@name="current"]'));
 		$this->indexStats->hasDeletions = $this->extractValue($xml->xpath(self::INDEX_XPATH.'*[@name="hasDeletions"]'));
 		$this->indexStats->lastModified = $this->extractValue($xml->xpath(self::INDEX_XPATH.'*[@name="lastModified"]'));
	}
	// ***
	
	
	
	//--|GETTER----------
	
	public function getActiveDocumentCount(){
		return intval($this->indexStats->numDocs);
	}
	
	
	
	public function getTotalDocumentCount(){
		return intval($this->indexStats->maxDoc);
	}
	
	
	
	public function getDeletedDocumentCount(){
		if( $this->indexStats->hasDeletions == 'true' ){
			return $this->getTotalDocumentCount() - $this->getActiveDocumentCount();
		} else {
			return 0;
		}
	}
	
	
	
	//--|FUNCTIONALITY----------
	
	public function optimizeIndexIfNecessary(&$solr){
		if( $this->getDeletedDocumentCount() >= $this->optimizeThreshold ){
			$solr->optimize();
		}
	}
	
	
	
	private function extractValue(Array $elem){
		$res = ''.$elem[0];
		return $res;
	}
	
}

?>