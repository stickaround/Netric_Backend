<?php
/**
 * Delete all field_defaults in app_object_field_defaults table.
 */
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);

// Do the same thing when updating the path
$db->beginTransaction();

// Do not timeout for this long query
$db->query('set statement_timeout to 0');

$result = $db->query("DELETE FROM app_object_field_defaults");

// Commit the transaction
$db->commitTransaction();