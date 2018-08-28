<?php

/**
 * Move workflow_instance object type to the new objects table
 * After creating the new objects table, copy the existing entities from the old table
 * This update is related to 018.php
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory as EntityDefinitionDataMapperFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityDefinition\EntityDefinition;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$log = $account->getApplication()->getLog();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
$entityDefinitionDataMapper = $serviceManager->get(EntityDefinitionDataMapperFactory::class);
$entityIndex = $serviceManager->get(IndexFactory::class);

/*
 * Since we are dealing with new object types, we will only specify only the new ones
 * and will not loop thru the /data/account/object-types.php
 */
$types = [
    ["obj_type" => "workflow_instance", "title" => "Workflow Instance", "revision" => "0", "system" => true],
    ["obj_type" => "workflow_action_scheduled", "title" => "Workflow Action Scheduled", "revision" => "0", "system" => true]
];

foreach ($types as $objDefData) {
    try {
        // Clear any cache for the definition
        $entityDefinitionLoader->clearCache($objDefData['obj_type']);

        // Reload fresh from the database
        $def = $entityDefinitionDataMapper->fetchByName($objDefData['obj_type']);

        // Make sure it has all the latest changes from the local data/entity_definitions/
        $entityDefinitionDataMapper->updateSystemDefinition($def);

        // Force a save to be sure all columns get created
        $entityDefinitionDataMapper->save($def);

        $log->info("Update 004.001.027 successfully moved the {$objDefData['obj_type']} entity definition to objects_table");
    } catch (\Exception $ex) {
        // If it fails, then we need to add it here
        $def = new EntityDefinition($objDefData['obj_type']);

        $def->fromArray($objDefData);

        // Make sure it has all the latest changes from the local data/entity_definitions/
        $entityDefinitionDataMapper->updateSystemDefinition($def);
        $entityDefinitionDataMapper->save($def);

        if (!$def->getId()) {
            $log->error("Update 004.001.027 failed to save entity definition {$objDefData['obj_type']}: " . $ex->getMessage());
        }
    }
}

$objectTypesToMove = [
    ['obj_type' => 'workflow_instance', 'old_table' => 'workflow_instances'],
    ['obj_type' => 'workflow_action_scheduled', 'old_table' => 'workflow_action_schedule'],
];

/**
 * Get an entity from the old table
 */
$loadEntityFromOldTable = function (&$entity, $id, $tableName, $database) {
    $def = $entity->getDefinition();
    $query = "SELECT * FROM $tableName WHERE id=:id";

    $result = $database->query($query, ["id" => $id]);
    if (!$result->rowCount()) {
        // The object was not found
        return false;
    }

    $row = $result->fetch();

    // Load data for foreign keys
    $all_fields = $def->getFields();
    foreach ($all_fields as $fname => $fdef) {
        // Populate values and foreign values for foreign entries if not set
        if ($fdef->type == "fkey" || $fdef->type == "object" || $fdef->type == "fkey_multi" || $fdef->type == "object_multi") {
            $mvals = null;

            // set values of fkey_multi and object_multi fields as array of id(s)
            if ($fdef->type == "fkey_multi" || $fdef->type == "object_multi") {
                if ($row[$fname]) {
                    $parts = json_decode($row[$fname], true);
                    if ($parts !== false) {
                        $row[$fname] = $parts;
                    }
                }

                // Was not set in the column, try reading from mvals list that was generated above
                if (!$row[$fname]) {
                    if (!$mvals && $row[$fname . "_fval"]) {
                        $mvals = json_decode($row[$fname . "_fval"], true);
                    }

                    if ($mvals) {
                        foreach ($mvals as $id => $mval) {
                            $row[$fname][] = $id;
                        }
                    }
                }
            }

            // Get object with no subtype - we may want to store this locally eventually
            // so check to see if the data is not already defined
            if (!$row[$fname] && $fdef->type == "object" && !$fdef->subtype) {
                if (!$mvals && $row[$fname . "_fval"]) {
                    $mvals = json_decode($row[$fname . "_fval"], true);
                }

                if ($mvals) {
                    foreach ($mvals as $id => $mval) {
                        $row[$fname] = $id; // There is only one value but it is assoc
                    }
                }
            }
        }

        switch ($fdef->type) {
            case "bool":
                $row[$fname] = ($row[$fname] == 't') ? true : false;
                break;
            case "date":
            case "timestamp":
                $row[$fname] = ($row[$fname]) ? strtotime($row[$fname]) : null;
                break;
            case 'object_multi':
                if ($fdef->subtype && is_array($row[$fname])) {
                    foreach ($row[$fname] as $index => $objectId) {
                        if (is_numeric($objectId)) {
                            $row[$fname][$index] = $objectId;
                        }
                    }
                }
                break;
        }

        // Check if we have an fkey label/name associated with column ids - these are cached in the object
        $fkeyValueName = (isset($row[$fname . "_fval"])) ? json_decode($row[$fname . "_fval"], true) : null;

        // Set entity value
        if (isset($row[$fname])) {
            $entity->setValue($fname, $row[$fname], $fkeyValueName);
        }
    }

    return true;
};

foreach ($objectTypesToMove as $objectType) {
    $objType = $objectType['obj_type'];
    $oldTable = $objectType['old_table'];

    // Get the entity definition
    $def = $entityDefinitionLoader->get($objType);

    $sql = "SELECT id FROM {$objectType['old_table']}";
    $result = $db->query($sql);
    $rows = $result->fetchAll();

    foreach ($rows as $row) {
        $oldEntityId = $row["id"];

        // We need to check first that the entity it was not moved yet
        $alreadyMovedId = $entityDataMapper->checkEntityHasMoved($def, $oldEntityId);
        if ($alreadyMovedId !== false) {
            $log->info(
                "Update 004.001.018 {$objType}.$oldEntityId already moved to $alreadyMovedId. Skipping"
            );
            continue;
        }

        // Load old entity data
        $oldEntity = $entityLoader->create($objType);
        $loadEntityFromOldTable($oldEntity, $oldEntityId, $oldTable, $db);
        $entityData = $oldEntity->toArray();

        // Create a new entity to save
        $newEntity = $entityLoader->create($objType);

        // Make sure that we set the id to null, so it will create a new entity record
        $entityData["id"] = null;

        // Parse the params of the entity
        $newEntity->fromArray($entityData);
        $newEntity->resetIsDirty();
        $newEntityId = $entityDataMapper->save($newEntity);

        if (!$newEntityId) {
            throw new \RuntimeException(
                sprintf(
                    "Could not save entity %s.%s: %s",
                    $objType,
                    $oldEntityId,
                    print_r($entityDataMapper->getErrors(), true)
                )
            );
        }

        $log->info(
            "Update 004.001.027 moved {$objType}.$oldEntityId to " .
            $def->getTable() . '.' . $newEntityId
        );

        // Now set the entity that it has been moved to new object table
        $entityDataMapper->setEntityMovedTo($def, $oldEntityId, $newEntityId);
    }
}
