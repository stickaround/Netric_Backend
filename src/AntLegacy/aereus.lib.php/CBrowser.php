<?php
/******************************************************************
Browser class
    
    Identifies the user's Operating system, browser and version
    by parsing the HTTP_USER_AGENT string sent to the server
    
    Typical Usage:
    
        require_once($_SERVER['DOCUMENT_ROOT'].'/include/browser.php');
        $br = new Browser;
        echo "$br->Platform, $br->Name version $br->Version";
    
    For operating systems, it will correctly identify:
        Microsoft Windows
        MacIntosh
        Linux

    Anything not determined to be one of the above is considered to by Unix
    because most Unix based browsers seem to not report the operating system.
    The only known problem here is that, if a HTTP_USER_AGENT string does not
    contain the operating system, it will be identified as Unix. For unknown
    browsers, this may not be correct.
    
    For browsers, it should correctly identify all versions of:
        Amaya
        Galeon
        iCab
        Internet Explorer
            For AOL versions it will identify as Internet Explorer (AOL) and the version
            will be the AOL version instead of the IE version.
        Konqueror
        Lynx
        Mozilla
        Netscape Navigator/Communicator
        OmniWeb
        Opera
        Pocket Internet Explorer for handhelds
        Safari
        WebTV
*****************************************************************/

class CBrowser
{
    var $Name = "Unknown";
    var $Version = "Unknown";
    var $Platform = "Unknown";
    var $UserAgent = "Not reported";
    var $AOL = false;

    function browser()
	{
        $agent = $_SERVER['HTTP_USER_AGENT'];

        // initialize properties
        $bd['platform'] = "Unknown";
        $bd['browser'] = "Unknown";
        $bd['version'] = "Unknown";
        $this->UserAgent = $agent;

        // find operating system
        if (preg_match("win", $agent))
            $bd['platform'] = "Windows";
        elseif (preg_match("mac", $agent))
            $bd['platform'] = "MacIntosh";
        elseif (preg_match("linux", $agent))
            $bd['platform'] = "Linux";
        elseif (preg_match("OS/2", $agent))
            $bd['platform'] = "OS/2";
        elseif (preg_match("BeOS", $agent))
            $bd['platform'] = "BeOS";

        // test for Opera        
        if (preg_match("opera",$agent))
		{
            $val = stristr($agent, "opera");
            if (preg_match("/", $val))
			{
                $val = explode("/",$val);
                $bd['browser'] = $val[0];
                $val = explode(" ",$val[1]);
                $bd['version'] = $val[0];
            }
			else
			{
                $val = explode(" ",stristr($val,"opera"));
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];
            }
        }
		// test for WebTV
		elseif(preg_match("webtv",$agent))
		{
            $val = explode("/",stristr($agent,"webtv"));
            $bd['browser'] = $val[0];
            $bd['version'] = $val[1];
        }
		// test for MS Internet Explorer version 1
		elseif(preg_match("microsoft internet explorer", $agent))
		{
            $bd['browser'] = "MSIE";
            $bd['version'] = "1.0";
            $var = stristr($agent, "/");
            if (preg_match("308|425|426|474|0b1", $var))
                $bd['version'] = "1.5";
        }
		// test for NetPositive
		elseif(preg_match("NetPositive", $agent))
		{
            $val = explode("/",stristr($agent,"NetPositive"));
            $bd['platform'] = "BeOS";
            $bd['browser'] = $val[0];
            $bd['version'] = $val[1];
        }
		// test for MS Internet Explorer
		elseif(preg_match("msie",$agent) && !preg_match("opera",$agent))
		{
            $val = explode(" ",stristr($agent,"msie"));
            $bd['browser'] = $val[0];
            $bd['version'] = $val[1];
        }
		// test for MS Pocket Internet Explorer
		elseif(preg_match("mspie",$agent) || preg_match('pocket', $agent))
		{
            $val = explode(" ",stristr($agent,"mspie"));
            $bd['browser'] = "MSPIE";
            $bd['platform'] = "WindowsCE";
            if (preg_match("mspie", $agent))
                $bd['version'] = $val[1];
            else 
			{
                $val = explode("/",$agent);
                $bd['version'] = $val[1];
            }
        }
		// test for Galeon
		elseif(preg_match("galeon",$agent))
		{
            $val = explode(" ",stristr($agent,"galeon"));
            $val = explode("/",$val[0]);
            $bd['browser'] = $val[0];
            $bd['version'] = $val[1];
        }
		// test for Konqueror
		elseif(preg_match("Konqueror",$agent))
		{
            $val = explode(" ",stristr($agent,"Konqueror"));
            $val = explode("/",$val[0]);
            $bd['browser'] = $val[0];
            $bd['version'] = $val[1];
        }
		// test for iCab
		elseif(preg_match("icab",$agent))
		{
            $val = explode(" ",stristr($agent,"icab"));
            $bd['browser'] = $val[0];
            $bd['version'] = $val[1];
        }
		// test for OmniWeb
		elseif(preg_match("omniweb",$agent))
		{
            $val = explode("/",stristr($agent,"omniweb"));
            $bd['browser'] = $val[0];
            $bd['version'] = $val[1];
		}
		// test for Phoenix
		elseif(preg_match("Phoenix", $agent))
		{
            $bd['browser'] = "Phoenix";
            $val = explode("/", stristr($agent,"Phoenix/"));
            $bd['version'] = $val[1];
        }
		// test for Firebird
		elseif(preg_match("firebird", $agent))
		{
            $bd['browser']="Firebird";
            $val = stristr($agent, "Firebird");
            $val = explode("/",$val);
            $bd['version'] = $val[1];
        }
		// test for Firefox
		elseif(preg_match("Firefox", $agent))
		{
            $bd['browser']="Firefox";
            $val = stristr($agent, "Firefox");
            $val = explode("/",$val);
            $bd['version'] = $val[1];
        }
		// test for Mozilla Alpha/Beta Versions
		elseif(preg_match("mozilla",$agent) && 
            preg_match("rv:[0-9].[0-9][a-b]",$agent) && !preg_match("netscape",$agent))
		{
            $bd['browser'] = "Mozilla";
            $val = explode(" ",stristr($agent,"rv:"));
            preg_match("rv:[0-9].[0-9][a-b]",$agent,$val);
            $bd['version'] = str_replace("rv:","",$val[0]);
        }
		// test for Mozilla Stable Versions
		elseif(preg_match("mozilla",$agent) &&
            preg_match("rv:[0-9]\.[0-9]",$agent) && !preg_match("netscape",$agent))
		{
            $bd['browser'] = "Mozilla";
            $val = explode(" ",stristr($agent,"rv:"));
            preg_match("rv:[0-9]\.[0-9]\.[0-9]",$agent,$val);
            $bd['version'] = str_replace("rv:","",$val[0]);
        }
		// test for Lynx & Amaya
		elseif(preg_match("libwww", $agent))
		{
            if (preg_match("amaya", $agent))
			{
                $val = explode("/",stristr($agent,"amaya"));
                $bd['browser'] = "Amaya";
                $val = explode(" ", $val[1]);
                $bd['version'] = $val[0];
            } 
			else 
			{
                $val = explode("/",$agent);
                $bd['browser'] = "Lynx";
                $bd['version'] = $val[1];
            }
        }
		// test for Safari
		elseif(preg_match("safari", $agent))
		{
            $bd['browser'] = "Safari";
            $bd['version'] = "";
        }
		// remaining two tests are for Netscape
		elseif(preg_match("netscape",$agent))
		{
            $val = explode(" ",stristr($agent,"netscape"));
            $val = explode("/",$val[0]);
            $bd['browser'] = $val[0];
            $bd['version'] = $val[1];
        }
		elseif(preg_match("mozilla",$agent) && !preg_match("rv:[0-9]\.[0-9]\.[0-9]",$agent))
		{
            $val = explode(" ",stristr($agent,"mozilla"));
            $val = explode("/",$val[0]);
            $bd['browser'] = "Netscape";
            $bd['version'] = $val[1];
        }
        
        // clean up extraneous garbage that may be in the name
        $bd['browser'] = ereg_replace("[^a-z,A-Z]", "", $bd['browser']);
        // clean up extraneous garbage that may be in the version        
        $bd['version'] = ereg_replace("[^0-9,.,a-z,A-Z]", "", $bd['version']);
        
        // check for AOL
        if (preg_match("AOL", $agent))
		{
            $var = stristr($agent, "AOL");
            $var = explode(" ", $var);
            $bd['aol'] = ereg_replace("[^0-9,.,a-z,A-Z]", "", $var[1]);
        }
        
        // finally assign our properties
        $this->Name = $bd['browser'];
        $this->Version = $bd['version'];
        $this->Platform = $bd['platform'];
        $this->AOL = $bd['aol'];
    }
}		
?>	