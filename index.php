<?php

//--|INCLUDES----------

require_once 'toolbox/ToolBox.class.php';

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

require_once 'php/test.class.php';
$testObj = new Test();
ToolBox::get()->Router->addShortRule('', 'Test:[php/test.class.php]/indexFunction/[rs]');
ToolBox::get()->Router->addShortRule('objecttest/(\d+)/(\w+)', 'Test/objectMethod/a:integer/b/', array('$2', '$1', array('test', 'test')));
ToolBox::get()->Router->addShortRule('objecttest2/(\d+)/(\w+)', '/objectMethod/a:integer/b/', null, $testObj);
ToolBox::get()->Router->addShortRule('statictest/(\w+)/(\d+)', 'Test/staticFunction/a/b:integer/[s]');
ToolBox::get()->Router->addShortRule('globaltest/(\w+)/(\w+)/(\d+)', ':[php/test.class.php]/globalFunction/a/b/c:integer/[r]');
ToolBox::get()->Router->addShortRule('globaltest/(\w+)', '/print_r/a/');
ToolBox::get()->Router->addShortRule('mixedgettest/(\w+)/(\w+)', '/mixedGetMethod/a/b/[g]', array('aa', 'bb', '$1', '$2'), $testObj);
ToolBox::get()->Router->addShortRule(404, 'Test:[php/test.class.php]/fourOfourFunction/[rs]');
ToolBox::get()->Router->exec();

?>

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
				
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">&nbsp;</td>
				</tr>
			</tfoot>
		</table>
	</body>
</html>