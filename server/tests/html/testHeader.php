<?php
	require_once("../../lib/AntConfig.php");
	require_once("ant.php");
	require_once("ant_user.php");
	
	$dbh = $ANT->dbh;
	$USERNAME = $USER->name;
	$USERID =  $USER->id;
	$ACCOUNT = $USER->accountId;
	$THEME = $USER->themeName;
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Tester</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" id='ant_css_base' href="/css/ant_base.css"> 
<link rel="stylesheet" id='ant_css_theme' href="/css/<?php echo $USER->themeCss; ?>"> 
<?php
	// Aereus lib
	include("lib/aereus.lib.js/js_lib.php");
	// ANT lib
	include("lib/js/includes.php");
?>
</head>
<body class='popup'>
