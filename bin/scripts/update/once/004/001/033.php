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

$result = $db->query("SELECT guid, id, field_data FROM objects");
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