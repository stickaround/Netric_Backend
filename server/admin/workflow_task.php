<?php
	require_once("../lib/AntConfig.php");
	require_once("ant.php");
	require_once("ant_user.php");
	require_once("lib/date_time_functions.php");
	require_once("lib/CPopup.awp");
	require_once("lib/Button.awp");
	require_once("lib/CDropdownMenu.awp");
	require_once("contacts/contact_functions.awp");
	require_once("customer/customer_functions.awp");
	require_once("lib/CAutoComplete.awp");
	require_once("lib/sms.php");
	require_once("lib/aereus.lib.php/CPageCache.php");
	
	$dbh = $ANT->dbh;
	$USERNAME = $USER->name;
	$USERID =  $USER->id;
	$ACCOUNT = $USER->accountId;
	$THEME = $USER->themeName;
	
	// Get forwarded variables
	$WID = $_GET['wid'];
	$LEAD_ID = $_GET['lead_id'];
	$OPPORTUNITY_ID = $_GET['opportunity_id'];
	$CUSTOMER_ID = $_GET['customer_id'];
	$CONTACT_ID = $_GET['contact_id'];
	$OPENER_ONSAVE = ($_GET['cbonsave']) ? base64_decode($_GET['cbonsave']) : '';
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
	// Add calendars
<?php
	echo "var g_wid = ".(($WID)?$WID:'null').";\n";
?>
	var g_theme = "<?php echo UserGetTheme($dbh, $USERID, 'name'); ?>";

	var g_workflow = new Object();
	// Initialize Workflow
	g_workflow.name = "My Workflow";

	function main()
	{
		document.getElementById("bdy").innerHTML = "Loading...";

		buildInterface();
	}

	function buildInterface()
	{
		var con = document.getElementById("bdy");
		con.innerHTML = "";

		var tb = new CToolbar();
		var btn = new CButton("Save &amp; Close", saveWorkflow, [true], "b1");
		tb.AddItem(btn.getButton(), "left");
		var btn = new CButton("Save Changes", saveWorkflow, null, "b1");
		tb.AddItem(btn.getButton(), "left");
		if (g_wid)
		{
			var btn = new CButton("Delete Workflow", "deleteWorkflow()", null, "b3");
			tb.AddItem(btn.getButton(), "left");
		}
		tb.print(document.getElementById('toolbar'));

		// Add title
		// --------------------------------------
		var dv = alib.dom.createElement("div", con);
		alib.dom.styleSet(dv, "margin", "3px 0px 3px 0px");
		var td = alib.dom.createElement("div", dv);
		alib.dom.styleSet(td, "float", "left");
		alib.dom.styleSet(td, "width", "75px");
		alib.dom.styleSet(td, "margin-top", "5px");
		alib.dom.styleSet(td, "margin-left", "3px");
		alib.dom.styleSetClass(td, "formLabel");
		td.innerHTML = "Event Name: ";
		var td = alib.dom.createElement("div", dv);
		alib.dom.styleSet(td, "margin-left", "80px");
		var txtTitle = alib.dom.createElement("input");
		txtTitle.type = "text";
		alib.dom.styleSet(txtTitle, "width", "98%");
		if (g_workflow.name) txtTitle.value = g_workflow.name;
		txtTitle.onchange = function() { g_workflow.name = this.value; }
		td.appendChild(txtTitle);

		// Add Condition
		// --------------------------------------
		var frm1 = new CWindowFrame("Run when the following conditions are met", null, "3px");
		var frmcon = frm1.getCon();
		frm1.print(con);

		// Add Tasks
		// --------------------------------------
		var frm1 = new CWindowFrame("Tasks", null, "0px");
		var frmcon = frm1.getCon();
		frm1.print(con);
		buildTasks(frmcon);
	}

	function buildTasks(con)
	{
		var tbl = new CToolTable("100%");
		tbl.addHeader("Name");
		tbl.addHeader("Do");
		tbl.addHeader("When");
		tbl.addHeader("Condition");
		tbl.addHeader("Delete", "center", "50px");
		tbl.print(con);

		// Add Tasks
		// --------------------------------------
		var rw = tbl.addRow();
		rw.addCell("Send Welcome Message");
		rw.addCell("Send Email");
		rw.addCell("When workflow is first run");
		rw.addCell("Where Status=New");

		var del_dv = alib.dom.createElement("div");
		rw.addCell(del_dv, true, "center");
		del_dv.innerHTML = "<img border='0' src='/images/themes/" + ((typeof(Ant)=='undefined')?g_theme:Ant.m_theme) + "/icons/deleteTask.gif' />";
		alib.dom.styleSet(del_dv, "cursor", "pointer");
		del_dv.m_rw = rw;
		//del_dv.m_id = id;
		del_dv.onclick = function()
		{
			ALib.Dlg.confirmBox("Are you sure you want to remove this task?", "Remove Task", [this.m_rw]);
			ALib.Dlg.onConfirmOk = function(row)
			{
				row.deleteRow();

				// Remove group from document
				/*
				for (var i = 0; i < g_event.reminders.length; i++)
				{
					if (g_event.reminders[i].id == id)
						g_event.reminders.splice(i, 1);
				}
				*/
			}
		}	

		var rw = tbl.addRow();
		rw.addCell("Invoice");
		rw.addCell("Create Object: Invoice");
		rw.addCell("1 month after workflow starts");
		rw.addCell("Where ANT Account=active and ANT Account is not null");

		var del_dv = alib.dom.createElement("div");
		rw.addCell(del_dv, true, "center");
		del_dv.innerHTML = "<img border='0' src='/images/themes/" + ((typeof(Ant)=='undefined')?g_theme:Ant.m_theme) + "/icons/deleteTask.gif' />";
		alib.dom.styleSet(del_dv, "cursor", "pointer");
		del_dv.m_rw = rw;
		//del_dv.m_id = id;
		del_dv.onclick = function()
		{
			ALib.Dlg.confirmBox("Are you sure you want to remove this task?", "Remove Task", [this.m_rw]);
			ALib.Dlg.onConfirmOk = function(row)
			{
				row.deleteRow();

				// Remove group from document
				/*
				for (var i = 0; i < g_event.reminders.length; i++)
				{
					if (g_event.reminders[i].id == id)
						g_event.reminders.splice(i, 1);
				}
				*/
			}
		}	
	}

	function saveWorkflow(close)
	{
	}

	/***********************************************************
	* Make sure a user knows they will lose their email on exit
	************************************************************/
	var G_OrigVals = new Array();
	GBL_CHECKFORDIRTY = false;
	
	window.onbeforeunload = confirmExit;
	function confirmExit()
	{
		var oRTE = document.getElementById("cmpbody");
		var changed = false;
		
		if (GBL_CHECKFORDIRTY && document.forms[0])
		{
			for (i = 0; i < document.forms[0].elements.length; i++)
			{
				switch (document.forms[0].elements[i].type)
				{
				case 'text':
				case 'select-one':
				case 'textarea':
					if (G_OrigVals[i] != document.forms[0].elements[i].value)
						changed = true;
					break;
				case 'checkbox':
					if (G_OrigVals[i] != document.forms[0].elements[i].checked)
						changed = true;
					break;
				}
			}
			
			if (changed)
				return "You have attempted to leave this page.  If you have made any changes to the fields without clicking the Save button, your changes will be lost.  Are you sure you want to exit this page?";
		}
	}
	
	function SetCurrentVals()
	{
		if (document.forms[0])
		{
			for (i = 0; i < document.forms[0].elements.length; i++)
			{					
				switch ( document.forms[0].elements[i].type)
				{
				case 'text':
				case 'select-one':
				case 'textarea':
					G_OrigVals[i] = document.forms[0].elements[i].value;
					break;
				case 'checkbox':
					G_OrigVals[i] = document.forms[0].elements[i].checked;
					break;
				}
			}
		}
	}
<?php
?>
</script>
<style type="text/css">
#listdiv
{
	width:212px;
	height:150px;
	border:1px solid #CCCCCC;
	overflow:auto;
}
</style>
</head>

<body class='popup' onLoad="main();SetCurrentVals();">
<div id='toolbar' class='popup_toolbar'></div>
<div id='bdy' class='popup_body'></div>
</body>
</html>

