<?php
	require_once("../../lib/AntConfig.php");
	require_once("ant.php");
	require_once("ant_user.php");
	require_once("lib/Olap.php");
	require_once("lib/date_time_functions.php");
	require_once("contacts/contact_functions.awp");
	require_once("customer/customer_functions.awp");
	require_once("lib/aereus.lib.php/CPageCache.php");
	require_once("datacenter/datacenter_functions.awp");
	require_once("security/security_functions.php");
	
	$dbh = $ANT->dbh;
	$USERNAME = $USER->name;
	$USERID =  $USER->id;
	$ACCOUNT = $USER->accountId;
	$THEME = $USER->themeName;
	
	// Create a test cube
	$olap = new Olap($dbh);
	$olap->deleteCube("tests/js/olap"); // Purge cube if already exists

	// Get new cube
	$cube = $olap->getCube("tests/js/olap");

	// Record an entry for each quarter
	$data = array(
		'page' => "/index.php",
		'country' => "USA",
		'time' => "1/1/2012",
	);
	$measures = array("hits" => 100);
	$cube->writeData($measures, $data);
	$data = array(
		'page' => "/about.php",
		'country' => "USA",
		'time' => "4/1/2012",
	);
	$measures = array("hits" => 75);
	$cube->writeData($measures, $data);
	$data = array(
		'page' => "/about.php",
		'country' => "USA",
		'time' => "7/1/2012",
	);
	$measures = array("hits" => 50);
	$cube->writeData($measures, $data);
	$data = array(
		'page' => "/about.php",
		'country' => "CANADA",
		'time' => "7/1/2012",
	);
	$measures = array("hits" => 50);
	$cube->writeData($measures, $data);
	$data = array(
		'page' => "/about.php",
		'country' => "USA",
		'time' => "10/1/2012",
	);
	$measures = array("hits" => 25);
	$cube->writeData($measures, $data);
	$data = array(
		'page' => "/about.php",
		'country' => "CANADA",
		'time' => "10/1/2012",
	);
	$measures = array("hits" => 25);
	$cube->writeData($measures, $data);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Object Editor</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="STYLESHEET" type="text/css" href="/css/<?php echo UserGetTheme(&$dbh, $USERID, 'css'); ?>">
<?php
	// Aereus lib
	include("lib/aereus.lib.js/js_lib.php");
	// ANT lib
	include("lib/js/includes.php");
?>
<script language="javascript" type="text/javascript">
	var wfid = <?php print(($wfid) ? $wfid : "null"); ?>;
    
	function main()
	{
		var cube = new OlapCube(null, null, "tests/js/olap");
		testMatrixTable(cube);
		testSummaryTable(cube);
		testTabularTable(cube);
		/*
		cube.onload = function()
		{
			testSummaryTable(this);
			testMatrixTable(this);
			testTabularTable(this);
		}

		var query = new OlapCube_Query();
		query.addMeasure("hits");
		query.addMeasure("count");
		query.addDimension("country");
		query.addDimension("time", "asc", "Q Y");
		//query.addFilter("and", "country", "is_equal", "US");
		//query.addFilter("or", "country", "is_equal", "CANADA");
		cube.loadData(query);


		var tbl = cube.getTable("typename");
		tbl.setFilter(query);
		 */
	}

	function testTabularTable(cube)
	{
		var hdr = alib.dom.createElement("h3", document.getElementById('bdy'), "Tabular");
		var con = alib.dom.createElement("div", document.getElementById('bdy'));

		var tbl = new OlapCube_Table_Tabular(cube);
		tbl.addColumn("country");
		tbl.addColumn("time", "asc", "Q Y");
		tbl.addFilter("and", "country", "is_equal", "USA");
		tbl.addFilter("or", "country", "is_equal", "CANADA");
		tbl.print(con);
	}

	function testSummaryTable(cube)
	{
		var hdr = alib.dom.createElement("h3", document.getElementById('bdy'), "Summary");
		var con = alib.dom.createElement("div", document.getElementById('bdy'));

		var tbl = new OlapCube_Table_Summary(cube);
		tbl.addRow("country");
		tbl.addRow("time", "asc", "Q Y");
		tbl.addFilter("and", "country", "is_equal", "USA");
		tbl.addFilter("or", "country", "is_equal", "CANADA");
		tbl.addMeasure("hits"); // Measures are the columns in a summary view
		tbl.print(con);
	}

	function testMatrixTable(cube)
	{
		var hdr = alib.dom.createElement("h3", document.getElementById('bdy'), "Matrix");
		var con = alib.dom.createElement("div", document.getElementById('bdy'));

		var tbl = new OlapCube_Table_Matrix(cube);
		tbl.addRow("country");
		tbl.addColumn("time", "asc", "Q Y");
		tbl.addMeasure("hits");
		tbl.addFilter("and", "country", "is_equal", "USA");
		tbl.addFilter("or", "country", "is_equal", "CANADA");
		tbl.print(con);
	}
	
</script>
<style type="text/css">
</style>
</head>

<body class='popup' onload="main();">
<div id='toolbar' class='popup_toolbar'></div>
<div id='bdy_outer'>
<div id='bdy' class='popup_body'>
<?php
?>
</div>
</div>
</body>
</html>
