<?php

/**
 * Scan the moved entities and update its references
 */
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityDefinition\Field;
use Netric\Log\LogFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$log = $serviceManager->get(LogFactory::class);
$db = $serviceManager->get(RelationalDbFactory::class);
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

$sql = "select objects_moved.object_id, objects_moved.moved_to, objects_moved.object_type_id, " .
       "app_object_types.name as obj_type from objects_moved, " .
       "app_object_types WHERE objects_moved.object_type_id=app_object_types.id";
$resultMoved = $db->query($sql);
$rowsMoved = $resultMoved->fetchAll();
foreach ($rowsMoved as $rowMoved) {
    $result = $db->query("SELECT * FROM app_object_types");
    $objectTypes = $result->fetchAll();
    foreach ($objectTypes as $objectTypeData) {
        $objTypeDef = $entityDefinitionLoader->get($objectTypeData["name"]);
        $fields = $objTypeDef->getFields();
        foreach ($fields as $field) {
            // Skip over any fields that are not a reference to an object
            if ($field->type != Field::TYPE_OBJECT && $field->type != Field::TYPE_OBJECT_MULTI) {
                continue;
            }

            // Log what is going on to track progress
            $log->info(
                "Update 004001023: Moving {$rowMoved["obj_type"]}.{$field->name}" .
                " from {$rowMoved['object_id']} to {$rowMoved['moved_to']}"
            );

            // Create an EntityQuery for each object type
            $oldFieldValue = null;
            $newFieldValue = null;

            // Check if field subtype is the same as the $def objtype and if field is not multivalue
            if ($field->subtype == $rowMoved['obj_type']) {
                $oldFieldValue = $rowMoved['object_id'];
                $newFieldValue = $rowMoved['moved_to'];
            }

            // Encode object type and id with generic obj_type:obj_id
            if (empty($field->subtype)) {
                $oldFieldValue =$rowMoved['obj_type'] . ':' . $rowMoved['object_id'];
                $newFieldValue = $rowMoved['obj_type'] . ':' . $rowMoved['moved_to'];
            }

            // Only continue if the field met one of the conditions above
            if (!$oldFieldValue || !$newFieldValue) {
                continue;
            }

            if ($field->type == Field::TYPE_OBJECT) {
                // If type = object then we will just need to update the id
                $db->query(
                    "UPDATE objects_{$objTypeDef->getObjType()}_act SET " .
                    "{$field->name}='{$newFieldValue}' " .
                    "WHERE {$field->name}='{$oldFieldValue}'"
                );
            } elseif ($field->type == Field::TYPE_OBJECT_MULTI) {
                // Replace array values in the field column that is json encoded
                $db->query(
                    "UPDATE objects_{$objTypeDef->getObjType()}_act SET " .
                    "{$field->name}=REPLACE({$field->name}, '\"{$oldFieldValue}\"', '\"{$newFieldValue}\"') " .
                    "WHERE {$field->name} LIKE '%{$oldFieldValue}%'"
                );
            }

            // Replace values in the cached field_fvals column
            if ($db->columnExists("objects_{$objTypeDef->getObjType()}_act", "{$field->name}_fvals")) {
                $db->query(
                    "UPDATE objects_{$objTypeDef->getObjType()}_act SET " .
                    "{$field->name}_fvals=REPLACE({$field->name}, '\"{$oldFieldValue}\"', '\"{$newFieldValue}\"') " .
                    "WHERE {$field->name}_fvals LIKE '%{$oldFieldValue}%'"
                );
            }
        }

        // Update object_associations
        $db->update(
            'object_associations',
            ['object_id'=>$rowMoved['moved_to']],
            ['type_id'=>$rowMoved['object_type_id'], 'object_id'=>$rowMoved['object_id']]
        );

        $db->update(
            'object_associations',
            ['assoc_object_id'=>$rowMoved['moved_to']],
            ['assoc_type_id'=>$rowMoved['object_type_id'], 'assoc_object_id'=>$rowMoved['object_id']]
        );
    }
}
