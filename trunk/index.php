<?php

//--|INCLUDES----------

require_once 'toolbox/toolbox.class.php';

if( isset($_GET['download-requestfile']) ){
	ToolBox::download()->requestFile($_GET['download-requestfile']);
	exit();
}

ToolBox::authentification()->registerSingleton();
ToolBox::get()->Authentificator->init();

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
				
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">&nbsp;</td>
				</tr>
			</tfoot>
		</table>
	</body>
</html>