<?php
/**
 * Move all object type IDs to obj_type names
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

$sql = "ALTER TABLE workflow_instances ADD COLUMN object_type CHARACTER VARYING(128)";
$dbh->Query($sql);

// Now convert old IDs to object_type strings
$sql = "UPDATE workflow_instances SET
        object_type=(
            SELECT name FROM app_object_types WHERE app_object_types.id=workflow_instances.object_type_id
        ) WHERE object_type is NULL;";
$results = $dbh->Query($sql);