<?php
/** 
 * Root level application initialization
 * 
 * This is similar to a 'main' routine in that it MUST be included in all executed scripts
 * because it is responsible for setting up and initializing the netric application and account.
 * 
 *  @author Sky Stebnicki <sky.stebnicki@aereus.com>
 *  @copyright 2014 Aereus
 */


// Setup autoloader
include(__DIR__ . "/init_autoloader.php");

// Initialize Netric Application and Account
// ------------------------------------------------
$config = new Netric\Config();

// Initialize application
$application = new Netric\Application\Application($config);

// Initialize account
//$account = $application->getAccount();

// Initialize the current user (if set)
// if ($_SESSION['user'])
//      $user = new Netric\User($account);
