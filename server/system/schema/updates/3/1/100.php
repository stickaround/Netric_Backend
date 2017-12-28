<?php
/**
 * Set number of comments for all status updates
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

// Find status updates
$slist = new CAntObjectList($dbh, "status_update");
$slist->getObjects();
for ($i = 0; $i < $slist->getNumObjects(); $i++)
{
	$statUpdate = $slist->getObject($i);

	$clist = new CAntObjectList($dbh, "comment");
	$clist->addCondition("and", "obj_reference", "is_equal", "status_update:" . $statUpdate->id);
	$clist->getObjects();
	$statUpdate->setValue("num_comments", $clist->getNumObjects());
}
