<?php
/**
 * Move all type IDs for workflow actions to names
 *
 * $ant is a global variable to this script and is already created by the calling class
 */
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/CAntObject.php");
require_once("src/AntLegacy/CAntObjectList.php");
require_once("src/AntLegacy/Ant.php");
require_once("src/AntLegacy/AntUser.php");
require_once("src/AntLegacy/WorkFlow.php");
require_once("src/AntLegacy/WorkFlow/Action.php");

if (!$ant)
    die("Update failed because $ ant is not defined");

$dbh = $ant->dbh;

$sql = "select id, type from workflow_actions where type_name is null and type is not null";
$num = $dbh->GetNumberRows($results = $dbh->Query($sql));
for ($i = 0; $i < $num; $i++)
{
    $row = $dbh->GetRow($results, $i);
    $typeName = WorkFlow_Action::getTypeNameFromId($row['type']);
    $dbh->Query("UPDATE workflow_actions SET type_name='$typeName' WHERE id='" . $row['id'] . "'");
}