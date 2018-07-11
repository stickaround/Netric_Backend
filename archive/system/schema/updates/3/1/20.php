<?php
/**
 * Deal with old message_id
 *
 * $ant is a global variable to this script and is already created by the calling class
 */
require_once("src/AntLegacy/CAntObject.php");
require_once("src/AntLegacy/CDatabase.awp");
require_once("src/AntLegacy/Ant.php");
require_once("src/AntLegacy/AntUser.php");

if (!$ant)
	exit;
$dbh = $ant->dbh;
$user = new AntUser($dbh, USER_ADMINISTRATOR);

// Cleanup old array columns in email_thread if it exists
if ($dbh->getColumnType("email_threads", "mailbox_id") == "integer")
{
	$dbh->Query("ALTER TABLE email_threads DROP COLUMN mailbox_id");
	$dbh->Query("ALTER TABLE email_threads ADD COLUMN mailbox_id text");
}
