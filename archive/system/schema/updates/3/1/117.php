<?php
/**
 * Fix table problem object_sync_partner_collection_init
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

if (!$dbh->TableExists("object_sync_partner_collection_init"))
{
	$dbh->Query("CREATE TABLE object_sync_partner_collection_init
				(
				  collection_id bigint,
				  parent_id bigint DEFAULT 0,
				  ts_completed timestamp with time zone,
				  CONSTRAINT object_sync_partner_collection_init_pid_fkey FOREIGN KEY (collection_id)
					  REFERENCES object_sync_partner_collections (id) MATCH SIMPLE
					  ON UPDATE CASCADE ON DELETE CASCADE
				)");
}
