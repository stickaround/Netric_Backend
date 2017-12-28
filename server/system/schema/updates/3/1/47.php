<?php
/**
 * This updates checks for the existence of an index on the dacl_id of the security_acle table
 *
 * $ant is a global variable to this script and is already created by the calling class
 */
require_once("src/AntLegacy/CAntObject.php");
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/Ant.php");
require_once("src/AntLegacy/AntUser.php");

if (!$ant)
	die("Update 47 failed because \$ant is not defined");

$dbh = $ant->dbh;

// Create index for dacl_id if it does not exist
if (!$dbh->indexExists("security_acle_dacl_idx"))
{
	$dbh->Query("CREATE INDEX security_acle_dacl_idx
				  ON security_acle
				  USING btree
				  (dacl_id );");
}
