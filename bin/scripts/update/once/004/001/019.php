<?php
/**
 * Remove all dashboard entity that has a uname: "activity" and scope is user
 */
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityLoaderFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$db = $serviceManager->get(RelationalDbFactory::class);

// Find all dashboard entity with uname "activity" and scope is not system
$sql = "SELECT * FROM objects_dashboard where uname = 'activity' and scope !='system'";
$result = $db->query($sql);

// Loop thru the results and delete the entities
foreach ($result->fetchAll() as $row) {
    $entity = $entityLoader->get("dashboard", $row['id']);
    $entityDataMapper->delete($entity);
}
