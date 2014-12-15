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
<link rel="STYLESHEET" type="text/css" href="/css/<?php echo UserGetTheme($dbh, $USERID, 'css'); ?>">
<?php
    // Aereus lib
    include("lib/aereus.lib.js/js_lib.php");
    // ANT lib
    include("lib/js/includes.php");
?>
<script language="javascript" type="text/javascript">
	/**
     * Load Ant script
	 */
	function loadAnt()
	{
		Ant.init(function() { main(); });
	}

    var g_userid = "<?php print($USERID); ?>";
    
    var navMain = null

    function main()
    {
        var mainCon = document.getElementById("bdy");

        navMain = new AntViewsRouter();
        navMain.defaultView = "Help";
        navMain.options.viewManager = new AntViewManager();
        navMain.options.viewManager.setViewsToggle(true); // Only view one view at a time at the root level
        navMain.onchange = function(path)
        {
            this.options.viewManager.load(path);
        }

        var homeView = navMain.options.viewManager.addView("Help", {}, mainCon); // Default view is called index
        homeView.render = function()
        {
            var app = new AntApp("help");
            app.main(this);
        }
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
