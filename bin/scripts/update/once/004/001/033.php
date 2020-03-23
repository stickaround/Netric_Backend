<?php
/**
 * Get all tasks in objects_task table, and update each task's object reference.
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\EntityDefinition\ObjectTypes;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityFactory = $serviceManager->get(EntityFactoryFactory::class);
$entityDm = $serviceManager->get(DataMapperFactory::class);

// Do the same thing when updating the path
$db->beginTransaction();

// Do not timeout for this long query
$db->query('set statement_timeout to 0');

$result = $db->query("SELECT id, guid, field_data FROM objects_task");

// Commit the transaction
$db->commitTransaction();

foreach ($result->fetchAll() as $rowData) {
    if (isset($rowData['id'])) {
        $entityData = json_decode($rowData['field_data'], true);
    
        if (empty($entityData['id'])) {
            $entityData['id'] = $rowData['id'];
        }

        if (empty($entityData['guid'])) {
            $entityData['guid'] = $rowData['guid'];
        }

        $entity = $entityFactory->create(ObjectTypes::TASK);
        $entity->fromArray($entityData);
        
        // Just load the entity and the object references will be updated to guid.
        $entityDm->getById($entity, $rowData['id']);
    }
}