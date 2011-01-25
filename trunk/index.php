﻿<?php

//--|INCLUDES----------

require_once 'toolbox/ToolBox.class.php';

ToolBox::authentification()->registerSingleton(ToolBoxModuleAuthentification::SINGLETON_AUTHENTIFICATOR);
ToolBox::routing()->registerSingleton(ToolBoxModuleRouting::SINGLETON_ROUTER);

if( isset($_GET['download-requestfile']) ){
	ToolBox::download()->requestFile($_GET['download-requestfile']);
	exit();
}

if( isset($_GET['authentification-Authentificator-login']) ){
	ToolBox::get()->Authentificator->login('test', 'test', array(array('login' => 'test', 'password' => '098f6bcd4621d373cade4e832627b4f6')));
}

if( isset($_GET['authentification-Authentificator-logout']) ){
	ToolBox::get()->Authentificator->logout();
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
				
				<!-- ToolBoxAuthentification->Authentificator -->
				<tr>
					<td>authentification->Authentificator</td>
					<td>login / logout</td>
					<td>
						Current status: <?=ToolBox::get()->Authentificator->loggedIn() ? 'logged in' : 'logged out'?>
						<a href="?authentification-Authentificator-login">login</a>
						<a href="?authentification-Authentificator-logout">logout</a>
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
				
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">&nbsp;</td>
				</tr>
			</tfoot>
		</table>
	</body>
</html>