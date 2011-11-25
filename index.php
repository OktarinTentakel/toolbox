<?php

set_include_path(
	dirname(__FILE__)
	.PATH_SEPARATOR.get_include_path()
);

//--|INCLUDES----------

require_once 'toolbox/ToolBox.class.php';
require_once 'php/simpletest/autorun.php';

ToolBox::authentication()->registerSingleton(ToolBoxModuleAuthentication::SINGLETON_AUTHENTICATOR);
ToolBox::routing()->registerSingleton(ToolBoxModuleRouting::SINGLETON_ROUTER);
ToolBox::storage()->registerSingleton(ToolBoxModuleStorage::SINGLETON_SQLITECONNECTION, array('DB_FILE' => 'db/db.sqlite'));

if( isset($_GET['download-requestfile']) ){
	ToolBox::download()->requestFile($_GET['download-requestfile']);
	exit();
}

if( isset($_GET['authentication-Authenticator-login']) ){
	ToolBox::get()->Authenticator->login('test', 'test', array(array('login' => 'test', 'password' => '098f6bcd4621d373cade4e832627b4f6')));
}

if( isset($_GET['authentication-Authenticator-logout']) ){
	ToolBox::get()->Authenticator->logout();
}

/*require_once 'php/test.class.php';
$testObj = new Test();
ToolBox::get()->Router->addShortRule('', 'Test:[php/test.class.php]/indexFunction/[rs]');
ToolBox::get()->Router->addShortRule('objecttest/(\d+)/(\w+)', 'Test/objectMethod/a:integer/b/', array('$2', '$1', array('test', 'test')));
ToolBox::get()->Router->addShortRule('objecttest2/(\d+)/(\w+)', '/objectMethod/a:integer/b/', null, $testObj);
ToolBox::get()->Router->addShortRule('statictest/(\w+)/(\d+)', 'Test/staticFunction/a/b:integer/[s]');
ToolBox::get()->Router->addShortRule('globaltest/(\w+)/(\w+)/(\d+)', ':[php/test.class.php]/globalFunction/a/b/c:integer/[r]');
ToolBox::get()->Router->addShortRule('globaltest/(\w+)', '/print_r/a/');
ToolBox::get()->Router->addShortRule('mixedgettest/(\w+)/(\w+)', '/mixedGetMethod/a/b/[g]', array('aa', 'bb', '$1', '$2'), $testObj);
ToolBox::get()->Router->addShortRule(404, 'Test:[php/test.class.php]/fourOfourFunction/[rs]');
ToolBox::get()->Router->exec();*/

//--|CLASS----------

class ToolBoxTestSuiteOutput extends SimpleReporter {
	
	// ***
	protected $_currentModuleName = '';
	protected $_currentMethodName = '';
	// ***
	
	
	
	//--|HELPERS----------
	
	protected function sendNoCacheHeaders() {
		if (! headers_sent()) {
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
		}
	}
	
	
	
	protected function htmlEntities($message) {
		return htmlentities($message, ENT_COMPAT, 'utf-8');
	}
	
	
	
	//--|OUTPUT----------
	
	protected function paintEntry($type, $resulttype, $caption, $message){
		$stepInfo = array_splice($this->getTestList(), 2, 2);
		
		if( $stepInfo[0] != $this->_currentModuleName ){
			echo '
				<h2>'.$stepInfo[0].'</h2>
			';
			
			$this->_currentModuleName = $stepInfo[0];
		}
		
		if( $stepInfo[1] != $this->_currentMethodName ){
			echo '
				<h3>'.$stepInfo[1].'</h3>
			';
			
			$this->_currentMethodName = $stepInfo[1];
		}
		
		echo '
			<div class="'.$type.'">
				<span class="'.$type.'">'.$caption.'</span>: '.$this->htmlEntities($message).'
			</div>
		';
	}
	
	
	
	public function paintHeader($test_name){
		$this->sendNoCacheHeaders();
		echo '
			<!doctype html>
			<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<title>ToolBox Testsuite</title>
					<link href="css/main.css" type="text/css" rel="Stylesheet">
				</head>
				<body>
					<h1>'.$test_name.'</h1>
		';
	}
	
	
	
	public function paintFooter($test_name){
		$failed = (($this->getFailCount() + $this->getExceptionCount()) > 0);
		
		echo '
					<div class="summary '.($failed ? 'failed' : '').'">
						'.$this->getTestCaseProgress().' / '.$this->getTestCaseCount().' test cases complete:
						<strong>'.$this->getPassCount().'</strong> passes,
						<strong>'.$this->getFailCount().'</strong> fails,
						<strong>'.$this->getExceptionCount().'</strong> exceptions
					</div>
				</body>
			</html>
		';
	}
	
	
	
	public function paintPass($message){
		parent::paintPass($message);
		self::paintEntry('pass', 'pass', 'Pass', $message);
	}
	
	
	
	public function paintFail($message){
		parent::paintFail($message);
		self::paintEntry('fail', 'fail', 'Fail', $message);
	}
	
	
	
	public function paintError($message) {
		parent::paintError($message);
		self::paintEntry('error', 'fail', 'Exception', $message);
	}
	
	
	
	public function paintException($exception) {
		parent::paintException($exception);
		$message =
			'Unexpected exception of type ['.get_class($exception).'] with message ['.$exception->getMessage()
			.'] in ['.$exception->getFile().' line '.$exception->getLine().']'
		;
		self::paintEntry('exception', 'fail', 'Exception', $message);
	}
	
	
	
	public function paintSkip($message){
		parent::paintSkip($message);
		self::paintEntry('skipped', 'pass', 'Skipped', $message);
	}
	
}



//--|CLASS----------

class ToolBoxTestSuite extends TestSuite {
	public function __construct(){
		$this->TestSuite('ToolBox Testsuite');
		$this->collect(dirname(__FILE__).'/testsuite', new SimplePatternCollector('/^.+\/ModuleTestCase\.[a-zA-Z]+\.class\.php$/'));
		$this->run(new ToolBoxTestSuiteOutput());
		exit;
	}
}

?>

<?php /*
<!doctype html>

<html>
	<head>
		<title>ToolBox-Framework Testcases</title>
	</head>
	<body>
		<h1>ToolBox-Framework Testcases</h1>
		<table>
			<thead>
				<tr>
					<th>module</th>
					<th>method</th>
					<th>cases</th>
					<th>error cases</th>
				</tr>
			</thead>
			<tbody>
			
				<!-- ToolBox::download()->requestFile() -->
				
				<tr>
					<td>download</td>
					<td>requestFile($file)</td>
					<td>
						All links must lead to a download prompt:<br>
						<a href="?download-requestfile=media/download.pdf">download pdf</a>
						<a href="?download-requestfile=media/download.png">download png</a>
						<a href="?download-requestfile=media/download.zip">download zip</a>
					</td>
					<td>
						Method must throw exception if nonexistent file is requested:<br>
						<?php 
							try {
								ToolBox::download()->requestFile('test');
							} catch(Exception $e){
								echo 'passed';
							}
						?>
					</td>
				</tr>
				
				<!-- ToolBoxJavascript::printGoogleAnalyticsCode() -->
				<tr>
					<td>javascript</td>
					<td>printGoogleAnalyticsCode</td>
					<td>
						Must be GA-include with auto-pageview at the bottom:<br>
						<pre><?=htmlspecialchars(ToolBox::javascript()->printGoogleAnalyticsCode('UA-XXXXX-X'))?></pre>
						Must be GA-include without auto-pageview:<br>
						<pre><?=htmlspecialchars(ToolBox::javascript()->printGoogleAnalyticsCode('UA-XXXXX-X', true))?></pre>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
				<!-- ToolBoxJavascript::printGoogleAnalyticsCode() -->
				<tr>
					<td>javascript</td>
					<td>printGoogleAnalyticsPageView</td>
					<td>
						Must be code for a pageview to /home/test:<br>
						<pre><?=htmlspecialchars(ToolBox::javascript()->printGoogleAnalyticsPageView('UA-XXXXX-X', '/home/etc'))?></pre>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
				<!-- ToolBoxAuthentification->Authenticator -->
				<tr>
					<td>authentication->Authenticator</td>
					<td>login / logout</td>
					<td>
						Current status: <?=ToolBox::get()->Authenticator->loggedIn() ? 'logged in' : 'logged out'?>
						<a href="?authentication-Authenticator-login">login</a>
						<a href="?authentication-Authenticator-logout">logout</a>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
				<!-- ToolBoxRouting->Router -->
				<tr>
					<td>routing->Router</td>
					<td>testroutes</td>
					<td>
						<a href="/toolbox">index</a>
						<a href="/toolbox/objecttest/1/a/">objecttest</a>
						<a href="/toolbox/objecttest2/1/a/">objecttest2</a>
						<a href="/toolbox/statictest/a/1">statictest</a>
						<a href="/toolbox/globaltest/a/b/1/">globaltest</a>
						<a href="/toolbox/globaltest/1">globaltest2</a>
						<a href="/toolbox/mixedgettest/x/y">mixedgettest</a>
						<a href="/toolbox/globaltest/a/1">404</a>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
				<!-- ToolBoxStorage->SqliteConnection -->
				<tr>
					<td>storage->SqliteConnection</td>
					<td>CRUD</td>
					<td>
						Simple Select:<br>
						<?php print_r(ToolBox::get()->SqliteConnection->query('SELECT * FROM Changelog WHERE 1 LIMIT 1;')); ?>
						<br><br>
						Updates (first processed must be 1, second 0):<br>
						<?php
							ToolBox::get()->SqliteConnection->exec('UPDATE Changelog SET processed = 1 WHERE 1');
							$tmp = ToolBox::get()->SqliteConnection->query('SELECT * FROM Changelog WHERE 1 LIMIT 1;');
							echo $tmp[0]['processed'];
						?>
						<br>
						<?php
							ToolBox::get()->SqliteConnection->exec('UPDATE Changelog SET processed = 0 WHERE 1');
							$tmp = ToolBox::get()->SqliteConnection->query('SELECT * FROM Changelog WHERE 1 LIMIT 1;');
							echo $tmp[0]['processed'];
						?>
						<br><br>
						Mass Execution (executes two updates as one operation, must be 0):<br>
						<?php
							ToolBox::get()->SqliteConnection->addExecQuery('UPDATE Changelog SET processed = 1 WHERE 1');
							ToolBox::get()->SqliteConnection->addExecQuery('UPDATE Changelog SET processed = 0 WHERE 1');
							ToolBox::get()->SqliteConnection->execAll();
							$tmp = ToolBox::get()->SqliteConnection->query('SELECT * FROM Changelog WHERE 1 LIMIT 1;');
							echo $tmp[0]['processed'];
						?>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
				<!-- ToolBoxVariable -->
				<tr>
					<td>variable</td>
					<td>createValueCheck</td>
					<td>
						Null-Checks (must be yes/no/yes/no):<br>
						<?php echo ToolBox::variable()->isNull(null, null, null) ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->isNull(null, 'a', null) ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->isNotNull(5, 'a', new StdClass()) ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->isNotNull(null, 'a', new StdClass()) ? 'yes' : 'no'; ?><br>
						<br>
						Empty-Checks (must be yes/no/yes/no):<br>
						<?php echo ToolBox::variable()->isEmpty(false, null, '0') ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->isEmpty(0, '3', 'false') ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->isNotEmpty('asd', new StdClass(), '01') ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->isNotEmpty('123', true, array()) ? 'yes' : 'no'; ?><br>
						<br>
						Rule-Checks (must be yes/no/yes/no):<br>
						<?php echo ToolBox::variable()->applyRuleToValues(function($val){ return $val > 5; }, array(7, 66, 123)) ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->applyRuleToValues(function($val){ return $val <= 42; }, array(7, 42, 123)) ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->applyRuleToValues(function($val){ return $val % 2 == 0; }, array(2, 42, 166)) ? 'yes' : 'no'; ?><br>
						<?php echo ToolBox::variable()->applyRuleToValues(function($val){ return is_bool($val); }, array(true, false, 0)) ? 'yes' : 'no'; ?><br>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
				<!-- ToolBoxArray -->
				<tr>
					<td>array</td>
					<td></td>
					<td>
						<?php print_r(ToolBox::_array_()->assocToObject(array('a' => 'b', 'c' => 42))); ?>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
				<!-- ToolBoxImage -->
				<tr>
					<td>image</td>
					<td>getDominantColors</td>
					<td>
						Must be an Array of two colors, where the first is very dark (near near black) and the second very bright (near white)<br>
						<?php print_r(ToolBox::image()->getDominantColors('media/download.png')); ?>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
				<!-- ToolBoxImage -->
				<tr>
					<td>image</td>
					<td>hexColorToDecArray</td>
					<td>
						Must be Array of 240s<br>
						<?php print_r(ToolBox::image()->hexColorToDecArray('f0f0f0')); ?>
					</td>
					<td>
						no error cases
					</td>
				</tr>
				
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">&nbsp;</td>
				</tr>
			</tfoot>
		</table>
	</body>
</html>

*/ ?>