<?php
/**
 * Queue spam messages for sa-learn
 *
 * $ant is a global variable to this script and is already created by the calling class
 */
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/CAntObject.php");
require_once("src/AntLegacy/CAntObjectList.php");
require_once("src/AntLegacy/Ant.php");
require_once("src/AntLegacy/AntUser.php");
require_once("src/AntLegacy/Dacl.php");

if (!$ant)
	die("Update failed because $ ant is not defined");

$dbh = $ant->dbh;

if (!$dbh->ColumnExists("object_sync_stats", "revision"))
    $dbh->Query("ALTER TABLE object_sync_stats ADD COLUMN revision integer;");