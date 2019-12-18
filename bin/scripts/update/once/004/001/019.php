<?php
/**
 * Remove all dashboard entity that has a uname: "activity" and scope is user
 */
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$db = $serviceManager->get(RelationalDbFactory::class);

// Check first if scope column exists in the objects_dashboard table
if ($db->columnExists("objects_dashboard", "scope")) {
    $sql = "SELECT * FROM objects_dashboard where uname = 'activity' and scope != 'system'";
} else {
    $sql = "SELECT * FROM objects_dashboard where field_data->>'uname' = 'activity' and field_data->>'scope' != 'system'";
}

// Find all dashboard entity with uname "activity" and scope is not system
$result = $db->query($sql);

// Loop thru the results and delete the entities
foreach ($result->fetchAll() as $row) {
    $entity = $entityLoader->get(ObjectTypes::DASHBOARD, $row['id']);
    $entityDataMapper->delete($entity);
}
