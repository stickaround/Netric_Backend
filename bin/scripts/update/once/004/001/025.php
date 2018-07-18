<?php
/**
 * Create UUIDs from IDs for every object
 */
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityFactoryFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityFactory = $serviceManager->get(EntityFactoryFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

$numNullObjects = 0;

// Page through 100,000 objects at once and update
do {
    // First create all UUIDs in the gid field
    $sql = "SELECT o.id, o.guid, t.name as obj_type FROM objects o, app_object_types t
            WHERE o.object_type_id=t.id AND o.field_data IS NULL LIMIT 100000";
    $result = $db->query($sql);
    $numNullObjects = $result->rowCount();
    $rows = $result->fetchAll();
    foreach ($rows as $row) {
        // Load the object up old school style (obj_type, id)
        $entity = $entityFactory->create($row['obj_type']);
        $entityDataMapper->getById($entity, $row['id']);
        // Encode the json and update the table row
        $db->update(
            'objects',
            ['field_data' => json_encode($entity->toArray)],
            ['guid' => $row['guid']]
        );
    }
} while ($numNullObjects > 0);
