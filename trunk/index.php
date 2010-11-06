<?php

require_once 'toolbox/ToolBox.class.php';

?>

<!doctype html>

<html>
	<head>
		<title>ToolBox Testcases</title>
	</head>
	<body>
		<h1>ToolBox Testcases</h1>
		<?=ToolBox::download()->requestFile('test')?>
	</body>
</html>