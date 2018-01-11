<?php

$account = $this->getAccount();
$log = $account->getApplication()->getLog();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get("Netric/Db/Relational/RelationalDb");
$dm = $serviceManager->get('Netric/EntityGroupings/DataMapper/EntityGroupingDataMapper');

$groupingTables = array(
    "activity_types" => array("refObjType" => "activity", "refFieldName" => "type_id"),
    "ic_groups" => array("refObjType" => "infocenter_document", "refFieldName" => "groups"),
    "product_categories" => array("refObjType" => "product", "refFieldName" => "categories"),
    "user_groups" => array("refObjType" => "user", "refFieldName" => "groups"),

    "customer_labels" => array("refObjType" => "customer", "refFieldName" => "groups"),
    "customer_stages" => array("refObjType" => "customer", "refFieldName" => "stage_id"),
    "customer_status" => array("refObjType" => "customer", "refFieldName" => "status_id"),

    "customer_lead_classes" => array("refObjType" => "lead", "refFieldName" => "class_id"),
    "customer_lead_queues" => array("refObjType" => "lead", "refFieldName" => "queue_id"),
    "customer_lead_rating" => array("refObjType" => "lead", "refFieldName" => "rating_id"),
    "customer_lead_sources" => array("refObjType" => "lead", "refFieldName" => "source_id"),
    "customer_lead_status" => array("refObjType" => "lead", "refFieldName" => "status_id"),

    "customer_objections" => array("refObjType" => "opportunity", "refFieldName" => "objection_id"),
    "customer_opportunity_stages" => array("refObjType" => "opportunity", "refFieldName" => "stage_id"),
    "customer_opportunity_types" => array("refObjType" => "opportunity", "refFieldName" => "type_id"),
    "customer_lead_sources" => array("refObjType" => "opportunity", "refFieldName" => "lead_source_id"),

    "customer_invoice_status" => array("refObjType" => "invoice", "refFieldName" => "status_id"),

    "project_bug_severity" => array("refObjType" => "case", "refFieldName" => "severity_id"),
    "project_bug_status" => array("refObjType" => "case", "refFieldName" => "status_id"),
    "project_bug_types" => array("refObjType" => "case", "refFieldName" => "type_id"),

    "xml_feed_groups" => array("refObjType" => "content_feed", "refFieldName" => "groups"),
    "xml_feed_post_categories" => array("refObjType" => "content_feed_post", "refFieldName" => "categories"),

    "project_priorities" => array("refObjType" => "project", "refFieldName" => "priority"),
    "project_groups" => array("refObjType" => "project", "refFieldName" => "groups"),

    "project_priorities" => array("refObjType" => "task", "refFieldName" => "priority"),
);

$index = $serviceManager->get("EntityQuery_Index");
$query = new \Netric\EntityQuery("user");

// Execute the query
$res = $index->executeQuery($query);

for ($i = 0; $i < $res->getNum(); $i++) {
    $ent = $res->getEntity($i);

    $groupingTables["contacts_personal_labels" . $ent->getId()] = array(
        "refObjType" => "contact_personal",
        "refFieldName" => "groups",
        "filters" => array(
            "user_id" => $ent->getId()
        ));

    $groupingTables["user_notes_categories" . $ent->getId()] = array(
        "refObjType" => "note",
        "refFieldName" => "groups",
        "filters" => array(
            "user_id" => $ent->getId()
        ));
}

// Loop thru the grouping tables
foreach ($groupingTables as $table => $details) {
    try {
        if (isset($details["filters"])) {
            $groupings = $dm->getGroupings($details["refObjType"], $details["refFieldName"], $details["filters"]);
        } else {
            $groupings = $dm->getGroupings($details["refObjType"], $details["refFieldName"]);
        }
    } catch (Exception $e) {
        $log->error("Update 004.001.015 failed to move fkey object table. " . $e->getMessage());
        continue;
    }

    $def = $serviceManager->get("Netric/EntityDefinition/EntityDefinitionLoader")->get($groupings->getObjType());

    // Get the field details
    $field = $def->getField($groupings->getFieldName());
    foreach ($groupings->getAll() as $grp) {
        $tableData = [];
        $tableData['object_type_id'] = $def->getId();
        $tableData['field_id'] = $field->id;

        if ($grp->name && $field->fkeyTable['title']) {
            $tableData[$field->fkeyTable['title']] = $grp->name;
        }

        if ($grp->color) {
            $tableData['color'] = $grp->color;
        }

        if ($grp->isSystem) {
            $tableData['f_system'] = $grp->isSystem;
        }

        if ($grp->sortOrder) {
            $tableData['sort_order'] = $grp->sortOrder;
        }

        if ($grp->parentId && isset($field->fkeyTable['parent'])) {
            $tableData[$field->fkeyTable['parent']] = $grp->parentId;
        }

        if ($grp->commitId) {
            $tableData['commit_id'] = $grp->commitId;
        }

        if ($grp->commitId) {
            $tableData['commit_id'] = $grp->commitId;
        }

        $data = $grp->toArray();
        foreach ($data["filter_fields"] as $name => $value) {
            // Make sure that the column name does not exists yet
            if (array_key_exists($name, $tableData)) {
                continue;
            }

            if ($value && $db->columnExists("object_groupings", $name)) {
                $tableData[$name] = $value;
            }
        }

        // Create where conditions to check if group data already in object_groupings table
        $whereConditionValues = array(
            "name" => $grp->name,
            "object_type_id" => $def->id,
            "field_id" => $field->id
        );

        $whereConditions = array(
            "name = :name",
            "object_type_id = :object_type_id",
            "field_id = :field_id"
        );

        // If definition is private, then we need to setup the filters
        if ($def->isPrivate) {
            foreach ($details["filters"] as $filter => $value) {
                $whereConditionValues[$filter] = $value;
                $whereConditions[] = "$filter = :$filter";
            }
        }

        $sql = "SELECT * from object_groupings WHERE " . implode(" and ", $whereConditions);
        $result = $db->query($sql, $whereConditionValues);

        // Group data already in the object_groupings table, we will just update the record and get the group id
        if ($result->rowCount() === 1) {
            $groupId = $result->fetch()['id'];
            $db->update("object_groupings", $tableData, ['id' => $groupId]);
        } else {

            // Insert a new record of group data in object_groupings table
            $groupId = $db->insert("object_groupings", $tableData);
        }

        // If we are dealing with fkey_multi field, then we need to replace the referenced field values
        if ($field->type === "fkey_multi") {
            $updateQuery = "UPDATE {$def->object_table}
                                SET {$groupings->getFieldName()} = REPLACE({$groupings->getFieldName()}, '\"{$grp->id}\"', '\"$groupId\"'),
                                    {$groupings->getFieldName()}_fval = REPLACE({$groupings->getFieldName()}_fval, '\"{$grp->id}\"', '\"$groupId\"')";

            // Update the table reference
            $db->query($updateQuery);
        } else {
            $updateData = [];
            $updateData[$groupings->getFieldName()] = $groupId;
            $updateData[$groupings->getFieldName() . "_fval"] = "'{\"$groupId\":\"{$grp->name}\"}'";

            // Update the table reference
            $db->update($def->object_table, $updateData, [$groupings->getFieldName() => $grp->id]);
        }
    }
}