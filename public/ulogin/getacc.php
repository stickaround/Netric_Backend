<?php    
/**
 * Get account informaiton from an email address
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 */
require_once(__DIR__ . "/../../src/AntLegacy/AntConfig.php");
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/AntSystem.php");

$ret = array("account"=>"", "username"=>"");

$eml = $_REQUEST['email'];
if ($eml)
{
	// Get the account and username from AntSystem
	$sys = new AntSystem();
	$ret = $sys->getAccountFromEmail($eml);
}

// Set response
echo json_encode($ret);
