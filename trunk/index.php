<?php

//--|INCLUDES----------

require_once 'toolbox/ToolBox.class.php';

?>

<!doctype html>

<html>
	<head>
		<title>ToolBox Testcases</title>
	</head>
	<body>
		<h1>ToolBox Testcases</h1>
		<pre><?=ToolBox::download()->requestFile('test')?></pre>
		<pre><?=htmlspecialchars(ToolBox::javascript()->printGoogleAnalyticsCode('UA-XXXXX-X'))?></pre>
		<pre><?=htmlspecialchars(ToolBox::javascript()->printGoogleAnalyticsCode('UA-XXXXX-X', true))?></pre>
		<pre><?=htmlspecialchars(ToolBox::javascript()->printGoogleAnalyticsPageView('UA-XXXXX-X', '/home/etc'))?></pre>
	</body>
</html>