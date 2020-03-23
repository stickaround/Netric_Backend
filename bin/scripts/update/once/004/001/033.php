<?php
/**
 * Get all entities in objects table, and update each entity's object reference.
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\DataMapper\EntityRdbDataMapper;
use Netric\Entity\EntityFactoryFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityDm = $serviceManager->get(EntityRdbDataMapper::class);
$entityFactory = $serviceManager->get(EntityFactoryFactory::class);

$result = $db->query("SELECT guid, field_data FROM objects");
foreach ($result->fetchAll() as $rowData) {
    $entityData = json_decode($rowData['field_data'], true);
    $entityData['guid'] = $rowData['guid'];
    $entity = $entityFactory->create($entityData['obj_type']);
    $entity->fromArray($entityData);
    $entityDm->updatObjectReferencesToGuid($entity);
}