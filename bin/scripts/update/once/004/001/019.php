<?php
/**
 * Remove all dashboard entity that has a uname: "activity" and scope is user
 */
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityIndex = $serviceManager->get(IndexFactory::class);

// Find all dashboard entity with uname "activity" and scope is not system
$query = new EntityQuery("dashboard");
$query->where("uname")->equals("activity");
$query->where("scope")->doesNotEqual("system");

// Get the results
$results = $entityIndex->executeQuery($query);
$totalNum = $results->getTotalNum();

// Loop over total num - the results will paginate as needed
for ($i = 0; $i < $totalNum; $i++) {

    // Get each contact
    $entity = $results->getEntity($i);
    $entityDataMapper->delete($entity);
}