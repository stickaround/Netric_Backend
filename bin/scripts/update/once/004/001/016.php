<?php
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\DataMapper\DataMapperFactory;

/**
 * Fix a bug with the previous update - xxx.xxx.015 - where we moved
 * all grouping tables from custom grouping tables to object_groupings.
 * For grouping_multi types, we never copied the new IDs to object_grouping_mem
 * so while the cached field in the entity was updated to the new object_groupings
 * id, any queries involving selecting entities that are a member of the grouping
 * would result in false until a user opened and re-saved each entit since the
 * datamapper would be responsible for inserting the entity and grouping ID
 * into object_grouping_mem.
 */
$account = $this->getAccount();
$log = $account->getApplication()->getLog();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$dm = $serviceManager->get(EntityGroupingDataMapperFactory::class);
$groupingsLoader = $serviceManager->get(GroupingLoaderFactory::class);
$entityDefinitionDataMapper = $account->getServiceManager()->get(DataMapperFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

// Make sure that the object_grouping_mem table still exists
if (!$db->tableExists("object_grouping_mem")) {
    return;
}

// Groupings moved from custom tables to generic object_groupings in previous update
$groupingTables = [
    ["refObjType" => "infocenter_document", "refFieldName" => "groups"],
    ["refObjType" => "product", "refFieldName" => "categories"],
    ["refObjType" => "user", "refFieldName" => "groups"],
    ["refObjType" => "contact_personal", "refFieldName" => "groups"],
    ["refObjType" => "note", "refFieldName" => "groups"],
    ["refObjType" => "customer", "refFieldName" => "groups"],
    ["refObjType" => "content_feed", "refFieldName" => "groups"],
    ["refObjType" => "content_feed_post", "refFieldName" => "categories"],
    ["refObjType" => "project", "refFieldName" => "groups"],
];

// Loop thru the grouping tables
foreach ($groupingTables as $details) {

    $objType = $details["refObjType"];
    $fieldName = $details["refFieldName"];

    // Get the entity definition based on the current $objType we are dealing with
    $def = $entityDefinitionLoader->get($objType);

    // Get the field details based on the current $fieldName
    $field = $def->getField($fieldName);

    // Check first if $fieldName column exists in the objects_* table
    if ($db->columnExists($def->object_table, $fieldName)) {
        $sql = "SELECT id, $fieldName FROM {$def->object_table} WHERE $fieldName!='[]' AND $fieldName is not null";
    } else {
        /*
         * If the $fieldName does not exists in the $def->object_table anymore,
         * it means that we have already implemented the changes in schema definition
         * which is putting all entity data in the field_data column.
         */
        $sql = "SELECT field_data->>'id' as id, field_data->>'$fieldName' as $fieldName FROM {$def->object_table} 
            WHERE field_data->>'$fieldName'!='[]' AND field_data->>'$fieldName' is not null";
    }
    
    // Query the group data from the old fkey table
    $result = $db->query($sql);

    // Loop thru each entry in the old fkey object table
    foreach ($result->fetchAll() as $row) {
        $groupValues = json_decode($row[$fieldName]);

        // If group is not existing in the object_groupings, then we need to create a new group
        if (is_array($groupValues)) {
            foreach ($groupValues as $groupId) {
                // Skip any null IDs
                if (!is_numeric($groupId)) {
                    continue;
                }

                $sql = "SELECT * FROM object_grouping_mem WHERE " .
                    "object_type_id=:object_type_id AND field_id=:field_id " .
                    "AND object_id=:object_id AND grouping_id=:grouping_id";
                $conditionValues = [
                    'object_type_id' => $def->id,
                    'field_id' => $field->id,
                    'object_id' => $row['id'],
                    'grouping_id' => $groupId,
                ];
                $groupingResult = $db->query($sql, $conditionValues);

                if ($groupingResult->rowCount() == 0) {
                    $db->insert('object_grouping_mem', $conditionValues);
                }
            }
        }
    }
}