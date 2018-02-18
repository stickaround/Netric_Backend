<?php
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\EntityGroupings\LoaderFactory;
use Netric\Entity\ObjType\UserEntity;

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
$groupingsLoader = $serviceManager->get(LoaderFactory::class);
$entityDefinitionDataMapper = $account->getServiceManager()->get("EntityDefinition_DataMapper");
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

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

    // Query the group data from the old fkey table
    $sql = "SELECT id, $fieldName FROM {$def->object_table} WHERE $fieldName!='[]' AND $fieldName is not null";
    $result = $db->query($sql);

    // Loop thru each entry in the old fkey object table
    foreach ($result->fetchAll() as $row) {
        $groupValues = json_decode($row[$fieldName]);

        // If group is not existing in the object_groupings, then we need to create a new group
        if (is_array($groupValues)) {
            foreach ($groupValues as $groupId) {
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