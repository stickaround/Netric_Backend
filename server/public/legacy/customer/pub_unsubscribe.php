<?php 
	// ant
    require_once(__DIR__ . "../../../src/AntLegacy/AntConfig.php");
	require_once("ant.php");
	require_once("src/AntLegacy/WindowFrame.awp");
	require_once("src/AntLegacy/content_table.awp");
	require_once("users/user_functions.php");
	require_once("src/AntLegacy/Email.php");
	require_once("src/AntLegacy/Button.awp");
	require_once("src/AntLegacy/AntUser.php");
	require_once("src/AntLegacy/email_functions.php");
	// ALIB
	require_once("src/AntLegacy/aereus.lib.php/CCache.php");
	//require_once("src/AntLegacy/aereus.lib.php/CSessions.php");
	// App
	require_once("customer_functions.awp");
	include_once("CCustomer.php");
	
	$dbh = $ANT->dbh;

	$CUSTID = $_GET['custid'];
	if ($CUSTID)
	{
		$cust = new CAntObject($dbh, "customer", $CUSTID);
		$cust->setValue("f_noemailspam", 't');
		$cust->save();
	}
	$em = $_GET['email'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<title>Bulk Mail Exclusion</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="STYLESHEET" type="text/css" href="/css/ant_os.css">
<?php
	include("../js/legacy/aereus.lib.js/js_lib.php");
?>
</head>
<body>
<?php
	WindowFrameStart("Bulk Mail Exclusion");
	if ($CUSTID)
		echo "Thank you for your feedback, you have been excluded from the mailing list.";
	else if ($em)
		echo "Thank you for your feedback. Your email address ($em) has been excluded from the mailing list.";

	WindowFrameEnd();
?>
</body>
</html>
