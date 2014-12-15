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
        
        var dmCon = new CDropdownMenu();
        dmCon.m_div.removeChild(dmCon.m_div.firstChild);
        
        alib.dom.styleSet(dmCon.m_table, "visibility", "hidden");
        
        var dmLink = dmCon.createLinkMenu("Chat (<span id='chatFriendOnline'>0</span>)");
        alib.dom.styleSet(dmLink, "float", "right");
        alib.dom.styleSet(dmLink, "marginRight", "300px");
        
        
        dmLink.m_dmCon = dmCon;
        dmLink.onclick = function()
        {            
            var chatMessengerCon = document.getElementById('chatMessengerCon');
            var chatClientPopup = document.getElementById('chatClientPopup');
            var antChatCon = document.getElementById('antChatCon');
            
            if(chatMessengerCon)
            {
                if(chatMessengerCon.style.display = "none")
                    alib.dom.styleSet(chatMessengerCon, "display", "block");                    
            }            
            alib.dom.styleSet(antChatCon, "visibility", "visible");
            
            alib.dom.styleSet(antChatCon, "position", "relative");
            alib.dom.styleSet(antChatCon, "float", "right");    
            alib.dom.styleSet(antChatCon, "left", "250px");
            antChatCon.style.removeProperty("width");
        }
        
        var messenger = new AntChatMessenger();        
        messenger.print(dmCon.m_div);
        
        mainCon.appendChild(dmLink);
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
