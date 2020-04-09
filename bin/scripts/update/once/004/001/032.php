<?php
/**
 * Change the grouping_id field type to varchar in object_grouping_mem table
 */
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);

// Make sure that the object_grouping_mem table still exists
if ($db->tableExists("object_grouping_mem")) {
  $db->query("ALTER TABLE object_grouping_mem ALTER COLUMN grouping_id TYPE character varying(256)");    
}
