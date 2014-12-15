<?php
	require_once("../../lib/AntConfig.php");
	require_once("ant.php");
	require_once("ant_user.php");
	
	$dbh = $ANT->dbh;
	$USERNAME = $USER->name;
	$USERID =  $USER->id;
	$ACCOUNT = $USER->accountId;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title><?php print($title); ?></title>
	<link rel="STYLESHEET" type="text/css" href="/css/<?php echo UserGetTheme(&$dbh, $USERID, 'css'); ?>">
	<style type='text/css'>
	html, body 
	{
		height: 100%;
		overflow: hidden; /* IE - force scrollbar inclusive of div width */
	}
	body 
	{
		overflow: auto;
	}

	/* TMP calednar calsses */

	.CCalHeader
	{
		width:14%;
		border-width: 1px 1px 0 0;
		border-style: solid;
		height:20px;
		font-weight:bold;
		text-align:center;
	}

	.CCalCell
	{
		border-bottom:1px solid;
		padding:2px;
		background-color:#e3e3e3;
	}

	.CCalCellToday
	{
		border-bottom:1px solid;
		padding:2px;
		background-color:yellow;
	}

	</style>
	<?php
		// Aereus lib
		include("lib/aereus.lib.js/js_lib.php");
		// ANT lib
		include("lib/js/includes.php");
	?>
	<script language="javascript" type="text/javascript" src="/help/help.js"></script>
	<script LANGUAGE="javascript" TYPE="text/javascript">
	function appMainLoaded()
	{
		var app = new CHelp();
		app.m_document = document;
		app.m_container = document.getElementById("appbody");
		app.main();
	}

	function resizeMain()
	{
		var appbody = document.getElementById("appbody");
	}

	</script>
</head>
<body onload="appMainLoaded();resizeMain();" style='margin:0px;padding:0px;'>
<div id='appnav'>
	<div id='appname'></div>
</div>
<div id='appbody'>
</div>
</body>
</html>
