<?php
	require_once("../../lib/AntConfig.php");
	require_once("ant.php");
	require_once("ant_user.php");
	require_once("lib/date_time_functions.php");
	require_once("contacts/contact_functions.awp");
	require_once("customer/customer_functions.awp");
	require_once("lib/sms.php");
	require_once("lib/aereus.lib.php/CPageCache.php");
	require_once("datacenter/datacenter_functions.awp");
	require_once("security/security_functions.php");    
	
	$dbh = $ANT->dbh;
	$USERNAME = $USER->name;
	$USERID =  $USER->id;
	$ACCOUNT = $USER->accountId;
	$THEME = $USER->themeName;
	
	// Get forwarded variables
	$OBJ_TYPE = $_GET['obj_type'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Object Editor</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="STYLESHEET" id='ant_css_base' type="text/css" href="/css/ant_base.css">
<link rel="STYLESHEET" id='ant_css_theme' type="text/css" href="/css/<?php echo $USER->themeCss; ?>">
<?php if (AntConfig::getInstance()->debug) { ?>
	<script language="javascript" type="text/javascript" src="/lib/aereus.lib.js/alib_full.js"></script>
	<?php include("lib/js/includes.php"); ?>
<?php } else { ?>
	<script language="javascript" type="text/javascript" src="/lib/aereus.lib.js/alib_full.cmp.js"></script>
	<script language="javascript" type="text/javascript" src="/lib/js/ant_full.cmp.js"></script>
<?php } ?>

<script language="javascript" type="text/javascript">
	/**
     * Load Ant script
	 */
	function loadAnt()
	{
		Ant.init(function() { main(); });
	}

	var navMain = null

	function main()
	{
		var mainCon = document.getElementById("bdy");

		var wiz = new AntWizard("EmailCampaign");
		wiz.show();
	}

    function resized()
    {
    }
	
</script>
<style type="text/css">
</style>
</head>

<body class='popup' onLoad="loadAnt();" onresize='resized()'>
<div id='toolbar' class='popup_toolbar'></div>
<div id='bdy_outer'>
<div id='bdy' class='popup_body'>
<?php
?>
</div>
</div>
</body>
</html>
