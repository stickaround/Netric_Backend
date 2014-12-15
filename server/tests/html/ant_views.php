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
<link rel="STYLESHEET" type="text/css" href="/css/<?php echo UserGetTheme(&$dbh, $USERID, 'css'); ?>">
<?php
	// Aereus lib
	include("lib/aereus.lib.js/js_lib.php");
	// ANT lib
	include("lib/js/includes.php");
?>
<script language="javascript" type="text/javascript">
	var g_userid = "<?php print($USERID); ?>";
    
	function main()
	{
		buildInterface();
	}

	var navMain = null

	function buildInterface()
	{
		var main_con = document.getElementById("bdy");

		// Print edition selector

        var editionNav = alib.dom.createElement("div", main_con);

        var a = alib.dom.createElement("a", editionNav);
        a.href = "#desktop";
        a.innerHTML = "Desktop";

        var a = alib.dom.createElement("a", editionNav);
        a.href = "#mobile";
        a.innerHTML = "Mobile";

		var editionDiv = alib.dom.createElement("div", main_con);
        var viewManager = new AntViewManager();
		viewManager.setViewsToggle(true); // Single page toggle

		navMain = new AntViewsRouter();
		navMain.options.viewManager = viewManager;
		navMain.onchange = function(path)
		{
			this.options.viewManager.load(path);
		}

		buildDesktopInterface(viewManager, editionDiv);
		buildMobileInterface(viewManager, editionDiv);
	}

	function buildDesktopInterface(viewManager, editionDiv)
	{
		var viewDesktop = viewManager.addView("desktop", {}, editionDiv);
        viewDesktop.render = function()
        {
			var navd = alib.dom.createElement("div", this.con);
			navd.innerHTML = "Tabs: <a href='#desktop/app'>Desktop App 1</a>";

			var appd = alib.dom.createElement("div", this.con);
			appd.style.border = "1px solid";
			appd.style.height = "300px";
			var viewApp = this.addView("app", {}, appd);
			viewApp.render = function()
			{
				this.setViewsToggle(true); // Subviews all toggle - if one is visible, the others are hidden

				var con = new CSplitContainer("verticle", "100%");
				con.resizable = true;
				
				var appNav = con.addPanel("200px");;
				var appMain = con.addPanel("*");
				con.print(this.con);

				var nb = new CNavBar();
				nb.print(appNav);
				var sec = nb.addSection("Actions");
				sec.addItem("New Customer", "/images/icons/plus.png", 
							function(view){ view.navigate("new"); }, 
							[this], "new");

				sec.addItem("Browse", "/images/icons/plus.png", 
							function(view){ view.navigate("browse"); }, 
							[this], "browse");

				var viewNew = this.addView("new", {nb:nb}, appMain);
				viewNew.render = function()
				{
					var ol = new AntObjectLoader("customer");
					ol.print(this.con);
					ol.curView = this;
					ol.onClose = function()
					{
						this.curView.parentView.navigate("browse");
					}
					ol.onRemove = function()
					{
					}
				}
				viewNew.onshow = function() { this.options.nb.itemChangeState(this.name, "on"); };

				var viewBrowser = this.addView("browse", {nb:nb}, appMain);
				viewBrowser.render = function()
				{
					var innerCon = alib.dom.createElement("div", this.con);
					innerCon.innerHTML = "Object Browser w/inline";
				}
				viewBrowser.onshow = function() { this.options.nb.itemChangeState(this.name, "on"); };

				// Load default
				this.navigate("new");
			}
        }
		viewDesktop.onshow = function() { };
		viewDesktop.onhide = function() { };
	}

	function buildMobileInterface(viewManager, editionDiv)
	{
		var viewMobile = viewManager.addView("mobile", {}, editionDiv);
		viewMobile.setViewsSingle(true); // Subviews will hide this view
        viewMobile.render = function()
        {
			this.con.innerHTML = "<a href='#mobile/app'>Mobile App 1</a>";

			var viewApp = this.addView("app", {});
			viewApp.setViewsSingle(true); // Subviews will hide this view
			viewApp.render = function()
			{
				this.setViewsToggle(true); // Subviews all toggle - if one is visible, the others are hidden
				var viewCon = alib.dom.createElement("div", this.con);
				this.options.viewCon = viewCon;
				var a = alib.dom.createElement("a", viewCon);
				a.href = "javascript:void(0);";
				a.view = this;
				a.onclick = function() { this.view.navigate("new"); }
				a.innerHTML = "New Customer";

				var a = alib.dom.createElement("a", viewCon);
				a.href = "javascript:void(0);";
				a.view = this;
				a.onclick = function() { this.view.navigate("browse"); }
				a.innerHTML = "Browse";

				var viewNew = this.addView("new", {});
				viewNew.render = function()
				{
					var ol = new AntObjectLoader("customer");
					ol.print(this.con);
					ol.curView = this;
					ol.onClose = function()
					{
						this.curView.parentView.navigate("browse");
					}
					ol.onRemove = function()
					{
					}
				}

				var viewBrowser = this.addView("browse", {});
				viewBrowser.render = function()
				{
					var innerCon = alib.dom.createElement("div", this.con);
					innerCon.innerHTML = "Object Browser w/inline";
					
					var viewCon = alib.dom.createElement("div", this.con);
					
					var ol = new CAntObjectBrowser("customer"); //email_thread email_message contact_personal
					ol.setAntView(this); //antView = this;
					ol.mobile = true;
					ol.viewmode = "details";

					ol.print(innerCon);
					
					
				}
				viewBrowser.onshow = function() { };
				viewBrowser.onhide = function() { };

				// Load default
			}
        }
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
