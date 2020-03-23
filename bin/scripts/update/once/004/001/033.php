<?php
/**
 * Get all entities in objects table, and update each entity's object reference.
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\Entity\EntityFactoryFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityDm = $serviceManager->get(DataMapperFactory::class);
$entityFactory = $serviceManager->get(EntityFactoryFactory::class);

// Do the same thing when updating the path
$db->beginTransaction();

// Do not timeout for this long query
$db->query('set statement_timeout to 0');

$result = $db->query("SELECT guid, id, field_data FROM objects");

// Commit the transaction
$db->commitTransaction();


foreach ($result->fetchAll() as $rowData) {
    $entityData = json_decode($rowData['field_data'], true);
    
    if (!$entityData || empty($entityData['obj_type'])) {
        continue;
    }

    if (empty($entityData['id'])) {
        $entityData['id'] = $rowData['id'];
    }

    $entityData['guid'] = $rowData['guid'];
    $entity = $entityFactory->create($entityData['obj_type']);
    $entity->fromArray($entityData);
    $entityDm->updatObjectReferencesToGuid($entity);
}