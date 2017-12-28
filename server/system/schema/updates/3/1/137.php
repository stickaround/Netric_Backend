<?php
/**
 * Add f_system to user_groups if it is missing for any reason (there was a bug for the aereus account where
 * this happend).
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

if (!$dbh->ColumnExists("user_groups", "f_system"))
    $dbh->Query("ALTER TABLE user_groups ADD COLUMN f_system bool;");