<?php
/**
 * Delete an account
 */
require_once("src/AntLegacy/AntConfig.php");
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/AntSystem.php");

error_reporting(E_ERROR | E_WARNING | E_PARSE);

if (!$_SERVER['argv'][1])
	die("Account name is required as first param");

$accountName = $_SERVER['argv'][1];

// TODO: should we back this up?
$sys = new AntSystem();
$sys->deleteAccount($accountName);
