<?php
/**
 * Move public schema to account schema if not alraedy set
 *
 * $ant is a global variable to this script and is already created by the calling class
 */
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/CAntObject.php");
require_once("src/AntLegacy/Ant.php");
require_once("src/AntLegacy/AntUser.php");
require_once("src/AntLegacy/Dacl.php");

if (!$ant)
	die("Update failed because $ ant is not defined");

$dbh = $ant->dbh;

if ($dbh->setSchema("acc_" . $ant->id) === false)
{
	$dbh->Query("ALTER SCHEMA public RENAME TO acc_" . $ant->id . ";");
	$dbh->setSchema("acc_" . $ant->id); // set for future updates
}
