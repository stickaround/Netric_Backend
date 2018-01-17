<?php

use Netric\EntityDefinitions\Field;
use Netric\EntityGroupings\EntityGroupings;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;

$account = $this->getAccount();
$log = $account->getApplication()->getLog();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$dm = $serviceManager->get(EntityGroupingDataMapperFactory::class);

$groupingTables = array(
    array("table" => "activity_types", "refObjType" => "activity", "refFieldName" => "type_id"),
    array("table" => "ic_groups", "refObjType" => "infocenter_document", "refFieldName" => "groups"),
    array("table" => "product_categories", "refObjType" => "product", "refFieldName" => "categories"),
    array("table" => "user_groups", "refObjType" => "user", "refFieldName" => "groups"),
    array("table" => "contacts_personal_labels", "refObjType" => "contact_personal", "refFieldName" => "groups"),
    array("table" => "user_notes_categories", "refObjType" => "note", "refFieldName" => "groups"),

    array("table" => "customer_labels", "refObjType" => "customer", "refFieldName" => "groups"),
    array("table" => "customer_stages", "refObjType" => "customer", "refFieldName" => "stage_id"),
    array("table" => "customer_status", "refObjType" => "customer", "refFieldName" => "status_id"),

    array("table" => "customer_lead_classes", "refObjType" => "lead", "refFieldName" => "class_id"),
    array("table" => "customer_lead_queues", "refObjType" => "lead", "refFieldName" => "queue_id"),
    array("table" => "customer_lead_rating", "refObjType" => "lead", "refFieldName" => "rating_id"),
    array("table" => "customer_lead_sources", "refObjType" => "lead", "refFieldName" => "source_id"),
    array("table" => "customer_lead_status", "refObjType" => "lead", "refFieldName" => "status_id"),

    array("table" => "customer_objections", "refObjType" => "opportunity", "refFieldName" => "objection_id"),
    array("table" => "customer_opportunity_stages", "refObjType" => "opportunity", "refFieldName" => "stage_id"),
    array("table" => "customer_opportunity_types", "refObjType" => "opportunity", "refFieldName" => "type_id"),
    array("table" => "customer_lead_sources", "refObjType" => "opportunity", "refFieldName" => "lead_source_id"),

    array("table" => "customer_invoice_status", "refObjType" => "invoice", "refFieldName" => "status_id"),

    array("table" => "project_bug_severity", "refObjType" => "case", "refFieldName" => "severity_id"),
    array("table" => "project_bug_status", "refObjType" => "case", "refFieldName" => "status_id"),
    array("table" => "project_bug_types", "refObjType" => "case", "refFieldName" => "type_id"),

    array("table" => "xml_feed_groups", "refObjType" => "content_feed", "refFieldName" => "groups"),
    array("table" => "xml_feed_post_categories", "refObjType" => "content_feed_post", "refFieldName" => "categories"),

    array("table" => "project_priorities", "refObjType" => "project", "refFieldName" => "priority"),
    array("table" => "project_groups", "refObjType" => "project", "refFieldName" => "groups"),

    array("table" => "project_priorities", "refObjType" => "task", "refFieldName" => "priority"),
);

// This will be used to cache the groupings
$existingGroupingsCache = [];

// Loop thru the grouping tables
foreach ($groupingTables as $details) {

    $table = $details["table"];
    $objType = $details["refObjType"];
    $fieldName = $details["refFieldName"];

    // Get the entity defintion based on the current $objType we are dealing with
    $def = $serviceManager->get(EntityDefinitionLoaderFactory::class)->get($objType);

    // Get the field details based on the current $fieldName
    $field = $def->getField($fieldName);

    /*
     * If the fkey object table is not existing, then there is no need to continue
     * Since the purpose of this update script is to copy the old data from fkey object table to object_groupings
     */
    if ($db->tableExists($table) === false) {
        continue;
    }

    // Query the group data from the old fkey table
    $sql = "SELECT * from $table";
    $result = $db->query($sql);

    // Loop thru each entry in the old fkey object table
    foreach ($result->fetchAll() as $row) {

        $filters = [];
        foreach ($field->fkeyTable['filter'] as $key => $filterField) {
            $filters[$key] = $row[$filterField];
        }

        // Filter results to this user of the object is private
        if ($def->isPrivate && !isset($filters["user_id"]) && !isset($filters["owner_id"])) {
            $log->error("Private entity type called but grouping has no filter defined - $objType");
        }

        // Create a new groupings where we will add the old group row into the object_groupings
        $newGroupings = new EntityGroupings($objType, $fieldName, $filters);

        // Create a grouping hash key so we can just reuse the groupings for the same $objType and $fieldName
        $groupingHash = "$objType, $fieldName " . $newGroupings->getFiltersHash($filters);

        // If cache key does not exist yet, then we will get the existing groupings and put it in cache
        if (!isset($existingGroupingsCache[$groupingHash])) {
            $existingGroupingsCache[$groupingHash] = $dm->getGroupings($objType, $fieldName, $filters);
        }

        $existingGrouping = $existingGroupingsCache[$groupingHash];

        // We cannot continue if we do not have a groupings set, so we will log it and continue with the next fkey table
        if (!$existingGrouping) {
            $log->error("Update 004.001.015 no existing groupings specificed objType: $objType. fieldName: $fieldName");
            continue;
        }

        $groupName = $row[$field->fkeyTable['title']];
        $group = $existingGrouping->getByName($groupName);

        // If group is not existing in the object_groupings, then we need to create a new group
        if ($existingGrouping->getByName($groupName) === false) {

            // Create a new group under the $newGroupings
            $group = $newGroupings->create($groupName);
            $group->isHeiarch = (isset($field->fkeyTable['parent'])) ? true : false;
            if (isset($field->fkeyTable['parent']) && isset($row[$field->fkeyTable['parent']]))
                $group->parentId = $row[$field->fkeyTable['parent']];
            $group->color = (isset($row['color'])) ? $row['color'] : "";
            if (isset($row['sort_order']))
                $group->sortOrder = $row['sort_order'];
            $group->isSystem = (isset($row['f_system']) && $row['f_system'] == 't') ? true : false;
            $group->commitId = (isset($row['commit_id'])) ? $row['commit_id'] : 0;

            // Add all additional fields which are usually used for filters
            foreach ($row as $pname => $pval) {
                if ($pname != $field->fkeyTable['key'] && !$group->getValue($pname))
                    $group->setValue($pname, $pval);
            }

            $newGroupings->add($group);
            $dm->saveGroupings($newGroupings);
        }

        /*
         * After saving the new group, then we need to update the cached existing groupings
         * This will prevent from adding duplicate group in the object_groupings table
         */
        $existingGrouping->add($group);

        // Get the key (usually id field) from the $row as we need it to update the referenced entities
        $oldFkeyId = $row[$field->fkeyTable['key']];

        // If we are dealing with fkey_multi field, then we need to replace the referenced field values which are stored as JSON encoded text.
        if ($field->type === $field::TYPE_GROUPING_MULTI) {
            $updateQuery = "UPDATE {$def->object_table}
                                SET {$fieldName} = REPLACE({$fieldName}, '\"$oldFkeyId\"', '\"{$group->id}\"'),
                                    {$fieldName}_fval = REPLACE({$fieldName}_fval, '\"$oldFkeyId\"', '\"{$group->id}\"')";

            // Update the table reference
            $db->query($updateQuery);
        } else {
            $updateData = [];
            $updateData[$fieldName] = $group->id;
            $updateData[$fieldName . "_fval"] = json_encode(array($group->id => $group->name));

            // Update the table reference
            $db->update($def->object_table, $updateData, [$fieldName => $oldFkeyId]);
        }
    }
}