<?php
	require_once("../../lib/AntConfig.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">    
    <title>Test loading a local HTML Document</title>
    <link rel="stylesheet" type="text/css" href="./jsunit/css/jsUnitStyle.css">
	<?php
		// Aereus lib
		include("../../lib/aereus.lib.js/js_lib.php");

		// ANT lib
		include("../../lib/js/includes.php");
	?>
    <script type="text/javascript" src="./jsunit/app/jsUnitCore.js"></script>
    <script type="text/javascript">
		function testGetObjects() 
		{
			var list = new AntObjectList("customer");
			list.async = false;
			list.getObjects();
			assertTrue(list.objects.length > 0);
        }
    </script>
</head>

<body>
<h1>JsUnit AntObjectLoader Test</h1>

<p>This page tests loading data documents asynchronously. To see them, take a look at the source.</p>
</body>
</html>
