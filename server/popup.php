<?php
	require_once("lib/AntConfig.php");
	require_once("ant.php");
	require_once("ant_user.php");
	require_once("lib/content_table.awp");
	require_once("lib/CToolTabs.awp");
	require_once("lib/WindowFrame.awp");
	require_once("users/user_functions.php");
	include_once("calendar_functions.awp");
	require_once("lib/date_time_functions.php");
	require_once("lib/CPopup.awp");
	require_once("lib/Button.awp");
	require_once("lib/CDropdownMenu.awp");
	require_once("contacts/contact_functions.awp");
	require_once("customer/customer_functions.awp");
	require_once("lib/CAutoComplete.awp");
	require_once("lib/aereus.lib.php/CPageCache.php");
	
	$dbh = $ANT->dbh;
	$USERNAME = $USER->name;
	$USERID =  $USER->id;
	$ACCOUNT = $USER->accountId;
	$THEME = $USER->themeName;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Edit Event</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="STYLESHEET" type="text/css" href="/css/<?php echo UserGetTheme($dbh, $USERID, 'css'); ?>">
<script language="javascript" type="text/javascript" src="/calendar/calendar_functions.js"></script>
<script language="javascript" type="text/javascript" src="/customer/customer_functions.js"></script>
<?php
	include("../lib/aereus.lib.js/js_lib.php");
?>
<script language="javascript" type="text/javascript">
<?php
?>
	var g_theme = "<?php echo UserGetTheme($dbh, $USERID, 'name'); ?>";
	var g_user_email = "<?php print(UserGetEmail($dbh, $USERID)); ?>";
	var g_username = "<?php print($USERNAME); ?>";

	function main()
	{
		var con = document.getElementById("bdy");
		con.innerHTML = "Loading...";

		var tb = new CToolbar();
		var btn = new CButton("Save &amp; Close", saveEvent, [null, true], "b1");
		tb.AddItem(btn.getButton(), "left");
		var btn = new CButton("Save Changes", saveEvent, null, "b1");
		tb.AddItem(btn.getButton(), "left");
		if (g_eid)
		{
			var btn = new CButton("Delete Event", "deleteEvent()", null, "b3");
			tb.AddItem(btn.getButton(), "left");
		}
		tb.print(document.getElementById('toolbar'));


	}
</script>
<style type="text/css">

</style>
</head>

<body class='popup' onLoad="main();">
<div id='toolbar' class='popup_toolbar'></div>
<div id='bdy' class='popup_body'></div>
</body>
</html>
