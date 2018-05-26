<?php
require_once(__DIR__ . "/../src/AntLegacy/AntConfig.php");
require_once("ant.php");
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/AntSystem.php");
require_once("src/AntLegacy/CBrowser.awp");
require_once("src/AntLegacy/user_functions.php");
require_once("src/AntLegacy/customer/customer_functions.awp");
require_once("src/AntLegacy/AntUser.php");
require_once('src/AntLegacy/ServiceLocatorLoader.php');

// Get new netric authentication service
if (!isset($dbh))
    $dbh = null;

$sl = ServiceLocatorLoader::getInstance($dbh)->getServiceLocator();
$authService = $sl->get("AuthenticationService");

$binfo = new CBrowser();
$antsys = new AntSystem();

// p will be raw if from a form, but encoded if from a get url
$fwdpage = isset($_POST["p"]) ? $_POST['p'] : "";
if (!$fwdpage && isset($_GET['p']))
    $fwdpage = base64_decode($_GET['p']);

$account = $ANT->accountName;

// Check if user name and password has been saved in cookie
if (isset($_REQUEST["user"]) && isset($_REQUEST["password"])) {
    $pass = $_REQUEST["password"];
    $username = strtolower($_REQUEST["user"]);
} else if ($ANT->getSessionVar('uname') && $ANT->getSessionVar('aname') && $ANT->getSessionVar('uid')) {
    $pass = "saved";
    $username = $ANT->getSessionVar('uname');
    $uid = $ANT->getSessionVar('uid');
}

if ($username && $pass && $account) {
    $acctinf = $antsys->getAccountInfoByName($account);
    if ($acctinf['id']) {
        // Set variables
        $ANT->setSessionVar('db', $acctinf['database']);
        $ANT->setSessionVar('dbs', $acctinf['server']);
        $ANT->setSessionVar('aid', $acctinf['id']);

        $dbh = $ANT->dbh;
        $ANT->id = $acctinf['id'];

        // Now check user table for user name and password combinations
        if (isset($uid))
            $ret = $uid;
        else
            $ret = AntUser::authenticate($username, $pass, $dbh);
    } else {
        $ret = false;
    }

    if ($ret) {

        $user = $ANT->getUser($ret);

        // Set variables
        $ANT->setSessionVar('uname', $username);
        $ANT->setSessionVar('uid', $ret);
        $ANT->setSessionVar('aid', $acctinf['id']);
        $ANT->setSessionVar('aname', $account);

        // Store the new authentication string
        $authString = $authService->authenticate($username, $pass);
        setcookie("Authentication", $authString, time() + 60 * 60 * 24 * 30);

        // Automatically determine timezone
        if (@function_exists(geoip_record_by_name) && @function_exists(geoip_time_zone_by_country_and_region) && $_SERVER['REMOTE_ADDR']) {
            $region = @geoip_record_by_name($_SERVER['REMOTE_ADDR']);
            if ($region)
                $ANT->setSessionVar('tz', geoip_time_zone_by_country_and_region($region['country_code'], $region['region']));
        }

        // Get default domain for this account
        $defDom = $ANT->getEmailDefaultDomain($account, $dbh);

        // Make sure default domain exists in the mailsystem
        $antsys->addEmailDomain($acctinf['id'], $defDom);

        // Make sure that an email account exists for each domain
        $emailAddress = $user->verifyEmailDomainAccounts(($pass != 'saved') ? $pass : null);

        // Make sure default groups exist
        $user->verifyDefaultUserGroups();

        // Make sure default team exists
        $user->verifyDefaultUserTeam();

        // Make sure the user has a customer number
        $user->getAereusCustomerId();

        // Make sure default users exist
        $user->verifyDefaultUsers();

        // Find out if trial period has expired
        if ($ANT->settingsGet("general/trial_expired") == 't')
            $fwdpage = "/wizard.php?wizard=expired";

        // Find out if account was suspended due to billing errors
        if ($ANT->settingsGet("general/suspended_billing") == 't')
            $fwdpage = "/wizard.php?wizard=ubilling";

        // Determine if this is the first time this users has logged in, if so, then redirect
        if ($ANT->settingsGet("general/acc_wizard_run") == 'f')
            $fwdpage = "/wizard.php?wizard=account";

        // Set last login variable
        $user->logLogin();

        // redirect to protected page
        if ($fwdpage)
            $page = $fwdpage;
        else if ($settings_redirect_to)
            $page = "/$settings_redirect_to";
        else {
            $page = "/main";
        }
    } else {
        // redirect to error page
        header("Location: logout.php?e=2&user=$user&account=$account&p=" . base64_encode($fwdpage));
        exit();
    }
} else {
    // redirect to error page
    header("Location: index.php");
    exit();
}

header("Location: " . $page);
exit();
