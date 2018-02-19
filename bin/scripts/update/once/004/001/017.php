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

// Get groups with heirarcy that need to be fixed
$groupingTables = [
    ["table" => "ic_groups", "refObjType" => "infocenter_document", "refFieldName" => "groups"],
    ["table" => "user_groups", "refObjType" => "user", "refFieldName" => "groups"],
    ["table" => "contacts_personal_labels", "refObjType" => "contact_personal", "refFieldName" => "groups"],
    ["table" => "user_notes_categories", "refObjType" => "note", "refFieldName" => "groups"],
    ["table" => "customer_labels", "refObjType" => "customer", "refFieldName" => "groups"],
    ["table" => "xml_feed_groups", "refObjType" => "content_feed", "refFieldName" => "groups"],
    ["table" => "xml_feed_post_categories", "refObjType" => "content_feed_post", "refFieldName" => "categories"],
    ["table" => "project_groups", "refObjType" => "project", "refFieldName" => "groups"],
];

// Loop thru the grouping tables
foreach ($groupingTables as $details) {
    $oldGroupingTable = $details["table"];
    $objType = $details["refObjType"];
    $fieldName = $details["refFieldName"];

    // Get the entity definition based on the current $objType we are dealing with
    $def = $entityDefinitionLoader->get($objType);

    // Get the field details based on the current $fieldName
    $field = $def->getField($fieldName);

    // Only copy heirarchy if the old grouping table exists
    if ($db->tableExists($oldGroupingTable) === false) {
        continue;
    }

    // Query the group data from the old fkey table
    // we order it by id DESC to make sure old IDs are updated in reverse
    // order. Otherwise we could end up with an orpahned child.
    $sql = "SELECT * FROM $oldGroupingTable ORDER BY id DESC";
    $result = $db->query($sql);

    // Loop thru each entry in the old fkey object table
    foreach ($result->fetchAll() as $row) {

        $filters = [];

        // Copy over any filters
        if (isset($field->fkeyTable['filter'])) {
            foreach ($field->fkeyTable['filter'] as $key => $filterField) {
                if (empty($row[$filterField]) != true) {
                    $filters[$key] = $row[$filterField];
                }
            }
        } else if ($def->isPrivate && (isset($row["user_id"]) || isset($row["owner_id"]))) {
            /*
             * Make sure that the filter has been set for private entities
             * object_groupings handles this automatically in the datamapper so fkeyTable['filter']
             * might be null
             */
            $filters['user_id'] = isset($row["user_id"]) ? $row['user_id'] : $row["owner_id"];
        }

        // Filter results to this user of the object is private
        if ($def->isPrivate && !isset($filters["user_id"]) && !isset($filters["owner_id"])) {
            echo "No user_id found for private groupings" . var_export($row, true) . "\n";
            $log->error("Private entity type called but grouping has no filter defined - $objType");
        }

        $groupings = $groupingsLoader->get($objType, $fieldName, $filters);

        /*
         * We cannot continue if we do not have a groupings set, so we will
         * log it and continue with the next fkey table
         */
        if (!$groupings) {
            $log->error("Update 004.001.017 no existing groupings specified objType: $objType. fieldName: $fieldName");
            continue;
        }

        // Get the key (usually id field) from the $row as we need it to update the referenced entities
        $oldFkeyId = $row[$field->fkeyTable['key']];
        $groupName = $row[$field->fkeyTable['title']];
        $oldParentId = empty($row['parent_id']) ? null : $row['parent_id'];
        $group = $groupings->getByName($groupName, $oldParentId);

        // If group is not existing in the object_groupings or it was already updated with the new parent_id
        if ($group === false) {
            continue;
        }
        
        // Update any object groupings with a parent_id that refers to the old
        $db->update(
            'object_groupings',
            ['parent_id' => $group->id], // new id
            ['parent_id' => $oldFkeyId, 'object_type_id' => $def->id] // where old id
        );

        /*
         * Note: We are not worried much about ID collision since the new gropings
         * are global and much higher, and the old groupings were individual
         * seqences and in update 15 we bumped the sequence to old + 100,000.
         */
    }
}