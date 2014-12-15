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
	$wfid = $_GET['wfid'];
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
		var workFlow = new WorkFlow(wfid);
		workFlow.object_type = "customer";

		if (wfid)
		{
			workFlow.onload = function()
			{
				printGrid(this);
			}
			workFlow.load();
		}
		else
		{
			// Add some test actions to load
			var act1 = workFlow.addAction(WF_ATYPE_SENDEMAIL);
			var act1_1 = act1.addAction(WF_ATYPE_UPDATEFLD);
			var act2 = workFlow.addAction(WF_ATYPE_SENDEMAIL);
			printGrid(workFlow);
		}


		var saveCon = alib.dom.createElement("a", document.getElementById("toolbar"));
		saveCon.innerHTML = "Save";
		saveCon.workFlow = workFlow;
		saveCon.href = "javascript:void(0);";
		saveCon.onclick = function()
		{
			this.workFlow.save();
		}
	}

	function printGrid(workFlow)
	{
		var grid = new WorkFlow_ActionsGrid(workFlow);
		grid.print(document.getElementById("bdy"));
	}

    function resized()
    {
    }
	
</script>
<style type="text/css">
</style>
</head>

<body class='popup' onload="main();" onresize='resized()'>
<div id='toolbar' class='popup_toolbar'></div>
<div id='bdy_outer'>
<div id='bdy' class='popup_body'>
<?php
?>
</div>
</div>
</body>
</html>
