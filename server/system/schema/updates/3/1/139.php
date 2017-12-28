<?php
/**
 * Correct issues caused with email messages dates being imported as 2070
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

$sql = "select id from objects_email_message where extract(year from message_date) = '2070'";
$num = $dbh->GetNumberRows($results = $dbh->Query($sql));
for ($i = 0; $i < $num; $i++)
{
    $row = $dbh->GetRow($results, $i);
    $obj = CAntObject::factory($dbh, "email_message", $row['id']);
    $obj->reparse();
    echo "Reparsed {$row['id']} - " . $obj->getValue("message_date") . "\n";
}