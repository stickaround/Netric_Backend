<?php

// ALIB
require_once("lib/AntConfig.php");
require_once("lib/Ant.php");

// ANT
// logout.php - destroys session and cookie and returns to login form

$onHourAgo = time() - 3600;
Ant::setSessionVar("uid", "", $onHourAgo);
Ant::setSessionVar("uname", "", $onHourAgo);
Ant::setSessionVar("aid", "", $onHourAgo);
Ant::setSessionVar("aname", "", $onHourAgo);

// redirect browser back to login page
$page = "index.php?e=".$_GET['e']."&p=".$_GET['p'];
if ($_REQUEST['user'])
	$page .= "&user=".$_REQUEST['user'];
if ($_REQUEST['account'])
	$page .= "&account=".$_REQUEST['account'];
header("Location: $page");
