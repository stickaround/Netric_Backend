<?php
/**
 * Get all the entities that have null value in sort_order column and update the value based on the ts_entered
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\EntityDefinition\ObjectTypes;

$account = $this->getAccount();
$user = $account->getAuthenticatedUser();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityFactory = $serviceManager->get(EntityFactoryFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);


$db->beginTransaction();

// Do not timeout for this long query
$db->query('set statement_timeout to 0');

$result = $db->query('SELECT * from entity WHERE sort_order IS NULL');

// Commit the transaction
$db->commitTransaction();

foreach ($result->fetchAll() as $row) {    
    // Load rows and set values in the entity
    $entityData = json_decode($row['field_data'], true);
    $accountId = $entityData['account_id'];

    switch ($entityData['obj_type']) {        
        case ObjectTypes::ACTIVITY:
        case ObjectTypes::NOTIFICATION:
            // We do not need to add sort_order to activities and notifications
            break;
        default:
            $entity = $entityFactory->create($entityData['obj_type'], $accountId);
            $entity->fromArray($entityData);
            
            $entity->setValue('sort_order', $entity->getValue("ts_entered"));
            $entityLoader->save($entity, $user);
        break;
    }    
}

