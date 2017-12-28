<?php
/**
 * Make sure user_status exists for the calendar_events table
 *
 * $ant is a global variable to this script and is already created by the calling class
 */
require_once("src/AntLegacy/CAntObject.php");
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/Ant.php");
require_once("src/AntLegacy/AntUser.php");

if (!$ant)
	die("Update failed because $ ant is not defined");

$dbh = $ant->dbh;

if (!$dbh->ColumnExists("calendar_events", "user_status"))
	$dbh->Query("ALTER TABLE calendar_events ADD COLUMN user_status integer;");
