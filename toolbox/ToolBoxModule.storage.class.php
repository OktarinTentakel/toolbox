<?php

//--|INCLUDES----------

require_once 'ToolBoxModule.absclass.php';



//--|CLASS----------

/**
 * ToolBoxModuleStorage bundles everything concerning ways to persistently store and retrieve data.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package modules
 * @subpackage procedures
 */
class ToolBoxModuleStorage extends ToolBoxModule {
	const SINGLETON_SQLITECONNECTION = 'SqliteConnection';
	const SINGLETON_SOLRLUKEHELPER = 'SolrLukeHelper';
	public static $SINGLETON_CLASSES = array(self::SINGLETON_SQLITECONNECTION, self::SINGLETON_SOLRLUKEHELPER);

	// ***
	public function __construct($moduleName, Array $addedArgs = null){
		parent::__construct($moduleName, $addedArgs);
	}
	// ***
	
}



//--|NESTED-SINGLETON-[SqliteConnection]----------

/**
 * SqliteConnection is a minimal sqlite-wrapper for PHPs Sqlite3-extension.
 * This class wraps opening, closing, execution and stack-execution of queries on
 * a sqlite-db.
 * 
 * Can be used in two ways, since this class can differentiate between a single query and a query stack.
 * Either open and close manually, having several requests and action inbetween or let the module handle
 * the connection, for performing single, atomic requests.
 * 
 * The class can also gather several queries to query in one atomic action.
 *
 * @author Sebastian Schlapkohl
 * @version 0.25 alpha
 * @package singletons
 * @subpackage procedures
 */
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
		
		if( class_exists('SQLite3') ){
			if( isset($args[self::DB_FILE]) ){
				$this->dbFile = ''.$args[self::DB_FILE];
			} else {
				$this->throwMissingSingletonDataException(self::DB_FILE);
			}
		} else {
			$this->throwMissingSingletonRessourceException('SQLite3');
		}
	}
	// ***
	
	
	
	//--|GETTER----------
	
	/**
	 * Returns the last created primary key for an open connection.
	 * If no connection exists or an error while retrieving the id occurrs null is returned.
	 * It makes no sense whatsoever to return this value without an open connection, since the value
	 * is always connection-based.
	 * 
	 * @return uint|null the last created primary key
	 */
	public function getLastCreatedId(){		
		return $this->isOpen ? array_shift($this->db->query('SELECT last_insert_rowid() as lastid;')->fetchArray(SQLITE3_ASSOC)) : null;
	}
	
	
	
	//--|QUESTIONS----------
	
	/**
	 * Returns the current db-status. Depends on the fact if a db-connection is currently present or not.
	 * 
	 * @return Boolean true/false
	 */
	public function isOpen(){
		return !is_null($this->db);
	}
	
	
	
	//--|FUNCTIONALITY----------
	
	/**
	 * Opens a connection to the defined db-file and keeps it that way until further notice.
	 */
	public function open(){
		$this->db = new SQLite3($this->dbFile);
	}
	
	
	
	/**
	 * Closes the currently open db-connection if any. 
	 */
	public function close(){
		if( $this->isOpen() ){
			$this->db->close();
			$this->db = null;
		}
	}
	
	
	
	/**
	 * Queries the db for a result using SQL.
	 * 
	 * @param String $query the query to execute on the db
	 * @param int $mode the SQLite3-mode to use on the result, normally associative array
	 * @return * the query result in the form dictated by the mode 
	 */
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
	
	
	
	/**
	 * Adds a SQL-query to the query stack, for future stack execution.
	 * 
	 * @param String $query the query to add to the stack
	 */
	public function addQuery($query){
		$this->queries[] = "$query";
	}
	
	
	
	/**
	 * Executes all queries on the query stack and collects all resultsets
	 * in an associative array, where the query is the key to the corresponding resultset.
	 * The stack is emptied upon completion.
	 * 
	 * @param int $mode mode with which to construct the resultsets, normally associative array
	 * @return Array collection of resultsets
	 */
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
	
	
	
	/**
	 * Executes a query without result on the db, such as an update or delete.
	 * 
	 * @param String $query the query to execute
	 */
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
	
	
	
	/**
	 * Adds a query to execute to the execution stack for future execution on the db.
	 * 
	 * @param String $query the query to add to the execution stack
	 */
	public function addExecQuery($query){
		$this->execQueries[] = "$query";
	}
	
	
	
	/**
	 * Executes all queries on the execution stack and empties the stack upon completion.
	 */
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

/**
* SolrLukeHelper is a helper class to gather information about a running Solr-instance and to trigger
* maintenance things like optimization processes in case of a certain fragmentation for
* example. This module does not provide Solr-methods in itself, for this please use a class like
* this PHP Solr-interface (http://code.google.com/p/solr-php-client/)
*
* @author Sebastian Schlapkohl
* @version 0.25 alpha
* @package singletons
* @subpackage procedures
*/
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
		parent::__construct($args);
		
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
	
	/**
	 * Returns the amount of non-deleted, active documents in the index.
	 * 
	 * @return uint number of active documents
	 */
	public function getActiveDocumentCount(){
		return intval($this->indexStats->numDocs);
	}
	
	
	
	/**
	 * Returns the total amount of documents in the index, including dead documents.
	 * 
	 * @return uint total number of documents
	 */
	public function getTotalDocumentCount(){
		return intval($this->indexStats->maxDoc);
	}
	
	
	
	/**
	 * Returns the number of deleted documents in the index.
	 * 
	 * @return uint the number of currently deleted documents
	 */
	public function getDeletedDocumentCount(){
		if( $this->indexStats->hasDeletions == 'true' ){
			return $this->getTotalDocumentCount() - $this->getActiveDocumentCount();
		} else {
			return 0;
		}
	}
	
	
	
	//--|FUNCTIONALITY----------
	
	/**
	 * Triggers an index-optimization run if a certain spread between active and total documents is reached.
	 * 
	 * @param Apache_Solr_Service $solr the Solr-instance to optimize in case
	 */
	public function optimizeIndexIfNecessary(&$solr){
		if( $this->getDeletedDocumentCount() >= $this->optimizeThreshold ){
			$solr->optimize();
		}
	}
	
	
	
	/**
	 * Convenience method to reduce Xpathed-result-sets to the first element only.
	 * 
	 * @param Array $elem result array from an xpath-request with only one expected result
	 */
	private function extractValue(Array $elem){
		$res = array_shift($elem);
		return is_null($res) ? null : "$res";
	}
	
}

?>