<?php

use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\EntityGroupings\LoaderFactory;
use Netric\Entity\ObjType\UserEntity;

$account = $this->getAccount();
$log = $account->getApplication()->getLog();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$dm = $serviceManager->get(EntityGroupingDataMapperFactory::class);
$groupingsLoader = $serviceManager->get(LoaderFactory::class);
$entityDefinitionDataMapper = $account->getServiceManager()->get("EntityDefinition_DataMapper");
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

// First remove stale foreign key constraints
$fkeys = [
    'customers' => [
        'customers_ownerid_fkey',
        'customers_sid_fkey',
        'customers_stageid_fkey',
    ],
    'customer_leads' => [
        'customer_leads_aid_fkey',
        'customer_leads_clsid_fkey',
        'customer_leads_custid_fkey',
        'customer_leads_qid_fkey',
        'customer_leads_rid_fkey',
        'customer_leads_sid_fkey',
        'customer_leads_status_fkey',
        'customer_leads_uid_fkey',
    ],
    'customer_opportunities' => [
        'customer_opportunities_aid_fkey',
        'customer_opportunities_cid_fkey',
        'customer_opportunities_lid_fkey',
        'customer_opportunities_lsid_fkey',
        'customer_opportunities_objid_fkey',
        'customer_opportunities_stage_fkey',
        'customer_opportunities_tid_fkey',
        'customer_opportunities_uid_fkey',
    ],
    'customer_invoices' => [
        'customer_invoices_aid_fkey',
        'customer_invoices_custid_fkey',
        'customer_invoices_status_fkey',
        'customer_invoices_tid_fkey',
        'customer_invoices_uid_fkey',
    ],
    'project_bugs' => [
        'project_bug_pid_fkey',
        'project_bugs_custid_fkey',
        'project_bugs_status_fkey',
        'project_bugs_suv_fkey',
        'project_bugs_type_fkey',
        'project_bugs_user_fkey',
    ],
    'projects' => [
        'projects_cid_fkey',
        'projects_custid_fkey',
        'projects_priority_fkey',
        'projects_template_fkey',
        'projects_user_fkey',
    ],
    'project_tasks' => [
        'project_tasks_cid_fkey',
        'project_tasks_custid_fkey',
        'project_tasks_fid_fkey',
        'project_tasks_posid_fkey',
        'project_tasks_priority_fkey',
        'project_tasks_project',
        'project_tasks_rid_fkey',
        'project_tasks_temp_task_fkey',
        'project_tasks_user_fkey'
    ],
];

foreach ($fkeys as $table => $constraints) {
    foreach ($constraints as $contraintToDrop) {
        $db->query("ALTER TABLE " . $table . " DROP CONSTRAINT IF EXISTS " . $contraintToDrop);
    }
}

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
    array("table" => "sales_order_status", "refObjType" => "sales_order", "refFieldName" => "status_id"),

    array("table" => "project_bug_severity", "refObjType" => "case", "refFieldName" => "severity_id"),
    array("table" => "project_bug_status", "refObjType" => "case", "refFieldName" => "status_id"),
    array("table" => "project_bug_types", "refObjType" => "case", "refFieldName" => "type_id"),

    array("table" => "xml_feed_groups", "refObjType" => "content_feed", "refFieldName" => "groups"),
    array("table" => "xml_feed_post_categories", "refObjType" => "content_feed_post", "refFieldName" => "categories"),

    array("table" => "project_priorities", "refObjType" => "project", "refFieldName" => "priority"),
    array("table" => "project_groups", "refObjType" => "project", "refFieldName" => "groups"),

    array("table" => "project_priorities", "refObjType" => "task", "refFieldName" => "priority"),
);

// Loop thru the grouping tables
foreach ($groupingTables as $details) {

    $table = $details["table"];
    $objType = $details["refObjType"];
    $fieldName = $details["refFieldName"];

    // Get the entity definition based on the current $objType we are dealing with
    $def = $entityDefinitionLoader->get($objType);

    // First make sure the entity definition is updated to the latest with new groupings files
    if ($entityDefinitionDataMapper->updateSystemDefinition($def)) {
        // Clear the cache in the entity definition loader so future requests pull the latest version
        $entityDefinitionLoader->clearCache($objType);
    }

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
    $sql = "SELECT * FROM $table";
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
            $log->error("Update 004.001.015 no existing groupings specified objType: $objType. fieldName: $fieldName");
            continue;
        }

        $groupName = $row[$field->fkeyTable['title']];
        $group = $groupings->getByName($groupName);

        // If group is not existing in the object_groupings, then we need to create a new group
        if ($group === false) {

            // Create a new group under the $newGroupings
            $group = $groupings->create($groupName);
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

            // Fix a problem where on some accounts we had multiple administrators
            if (strtolower($groupName) == 'administrators'
                && $objType === 'user' && $fieldName === 'groups') {
                $group->id = UserEntity::GROUP_ADMINISTRATORS;
                $group->name = "Administrators";
            }

            $groupings->add($group);
            $groupingsLoader->save($groupings);
        }

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