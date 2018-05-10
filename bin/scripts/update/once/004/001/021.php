<?php
/**
 * Update the user entities to have a view/edit permission of the owner of the entity
 */
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$entityIndex = $serviceManager->get(IndexFactory::class);


// Find all dashboard entity with scope = system
$query = new EntityQuery("user");
$query->where("id")->isGreaterThan(0);

// Get the results
$results = $entityIndex->executeQuery($query);
$totalNum = $results->getTotalNum();

// Loop over total num - the results will paginate as needed
for ($i = 0; $i < $totalNum; $i++) {

    // Get each contact
    $entity = $results->getEntity($i);

    // Just save the user entity, the Entity/ObjType/UserEntity extension will update the dacl field.
    $entityDataMapper->save($entity);
}
