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

		/**
		 * Ant Objects have the ability to set values with a function setData
		 */
		function testSetData() 
		{
			var data = new Object();
			data.id = 1001;
			data.first_name = "sky";
			// Test fkey_multi
			data.groups = [[1, "My Test Group"], [2, "My Second Test Group"]];
			// Test fkey
			data.status_id = [1, "My Test Status"];

			var obj = new CAntObject("customer");
			obj.setData(data);

			var key = obj.getValue("status_id");
			//assertEquals(key, data.status[0]);

			var value = obj.getValueName("status_id");
			//assertEquals(key, data.status[1]);

			var groups = obj.getMultiValues("groups");
			//assertEquals(obj.getValue("first_name"), data.first_name);
        }
    </script>
</head>

<body>
<h1>JsUnit AntObjectLoader Test</h1>

<p>This page tests loading data documents asynchronously. To see them, take a look at the source.</p>
</body>
</html>
