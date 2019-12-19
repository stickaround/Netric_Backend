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
    $db->query("SET statement_timeout=0");
    $sql = "SELECT 
                o.id, o.guid, t.name as obj_type FROM objects o, app_object_types t
            WHERE 
                o.object_type_id=t.id 
                AND (o.field_data IS NULL OR o.field_data='null') 
            LIMIT 100000";
    $result = $db->query($sql);
    $numNullObjects = $result->rowCount();
    $rows = $result->fetchAll();
    foreach ($rows as $row) {
        // Create a new entity to fill
        $entity = $entityFactory->create($row['obj_type']);

        // Get column values which is how we used to store field values
        $def = $entity->getDefinition();
        $resultEntity = $db->query(
            "select * from {$def->getTable()} where id=:id",
            ["id" => $row['id']]
        );
        $entityRow = $resultEntity->fetch();
        $allFields = $def->getFields();
        foreach ($allFields as $fieldDefinition) {
            $entityDataMapper->setEntityFieldValueFromRow($entity, $fieldDefinition, $entityRow);
        }

        // Encode the json and update the table row
        $db->update(
            'objects',
            ['field_data' => json_encode($entity->toArray())],
            ['guid' => $row['guid']]
        );
    }
} while ($numNullObjects > 0);
