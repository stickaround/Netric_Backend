<?php
/**
 * Move all custom table entities over to objects_* table so that we no longer
 * have to deal with custom tables from entities
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

$log->info("UpdateOnce018 Initiated.");

// Get object types for each account
$types = require(__DIR__ . "/../../../../../../data/account/object-types.php");

/*
 * Loop through each type and update each object type definition
 * It is important that we update the object type definition first before moving the entities
 * So we can make sure that the object types will be using the new objects table
 */
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

        $log->info("Update 004.001.018 successfully moved the {$objDefData['obj_type']} entity definition to objects_table");
    } catch (\Exception $ex) {
        // If it fails, then we need to add it here
        $def = new EntityDefinition($objDefData['obj_type']);

        $def->fromArray($objDefData);
        $entityDefinitionDataMapper->save($def);

        if (!$def->getId()) {
            $log->error("Update 004.001.018 failed to save entity definition {$objDefData['obj_type']}: " . $ex->getMessage());
        }
    }
}

$objectTypesToMove = [
    ['obj_type' => 'invoice', 'old_table' => 'customer_invoices'],
    ['obj_type' => 'discussion', 'old_table' => 'discussions'],
    ['obj_type' => 'content_feed', 'old_table' => 'xml_feeds'],
    ['obj_type' => 'content_feed_post', 'old_table' => 'xml_feed_posts'],
    ['obj_type' => 'project_milestone', 'old_table' => 'project_milestones'],
    ['obj_type' => 'task', 'old_table' => 'project_tasks'],
    ['obj_type' => 'calendar_event', 'old_table' => 'calendar_events'],
    ['obj_type' => 'report', 'old_table' => 'reports'],
    ['obj_type' => 'user', 'old_table' => 'users'],
    ['obj_type' => 'comment', 'old_table' => 'comments'],
    ['obj_type' => 'lead', 'old_table' => 'customer_leads'],
    ['obj_type' => 'case', 'old_table' => 'project_bugs'],
    ['obj_type' => 'project', 'old_table' => 'projects'],
    ['obj_type' => 'note', 'old_table' => 'user_notes'],
    ['obj_type' => 'time', 'old_table' => 'project_time'],
    ['obj_type' => 'product_family', 'old_table' => 'product_families'],
    ['obj_type' => 'opportunity', 'old_table' => 'customer_opportunities'],
    ['obj_type' => 'product', 'old_table' => 'products'],
    ['obj_type' => 'invoice_template', 'old_table' => 'customer_invoice_templates'],
    ['obj_type' => 'infocenter_document', 'old_table' => 'ic_documents'],
    ['obj_type' => 'calendar_event_proposal', 'old_table' => 'calendar_event_coord'],
    ['obj_type' => 'customer', 'old_table' => 'customers'],
    ['obj_type' => 'approval', 'old_table' => 'workflow_approvals'],
    ['obj_type' => 'member', 'old_table' => 'members'],
    ['obj_type' => 'sales_order', 'old_table' => 'sales_orders'],
    ['obj_type' => 'product_review', 'old_table' => 'product_reviews'],
    ['obj_type' => 'dashboard', 'old_table' => 'dashboard'],
    ['obj_type' => 'calendar', 'old_table' => 'calendars'],
    ['obj_type' => 'workflow', 'old_table' => 'workflows'],
    ['obj_type' => 'workflow_action', 'old_table' => 'workflow_actions'],
];

foreach ($objectTypesToMove as $objectType) {
    $objType = $objectType['obj_type'];
    $oldTable = $objectType['old_table'];

    // Get the entity definition
    $def = $entityDefinitionLoader->get($objType);

    // Get old entities that are not yet moved
    $sql = "SELECT oldTable.* FROM $oldTable as oldTable WHERE oldTable.id NOT IN (SELECT object_id FROM objects_moved WHERE object_id = oldTable.id AND object_type_id=:object_type_id)";
    $result = $db->query($sql, ["object_type_id" => $def->getId()]);
    $rows = $result->fetchAll();

    foreach ($rows as $row) {
        $oldEntityId = $row["id"];
        $oldEntity = $entityLoader->create($objType);

        // Load row and set values in the old entity
        $allFields = $def->getFields();
        foreach ($allFields as $fieldDefinition) {

            // Sanitize the entity value.
            $value = $entityDataMapper->sanitizeDbValuesToEntityFieldValue($fieldDefinition, $row[$fieldDefinition->name]);

            // Set entity value
            $oldEntity->setValue($fieldDefinition->name, $value);
        }

        // Create a new entity to save
        $newEntity = $entityLoader->create($objType);
        
        // Make sure that we set the id to null, so it will create a new entity record
        $entityData = $oldEntity->toArray();
        $entityData["id"] = null;

        // If this is a customer remove uname since we no longer use it
        if ($objType == 'customer') {
            $entityData['uname'] = '';
        }

        // Overcome a bug where we accidentally created many activity dasbhoards
        if ($objType == 'dashboard' && $entityData['uname'] == 'activity') {
            $entityData['uname'] = '';
        }

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
            "Update 004.001.018 moved {$objType}.$oldEntityId to " .
            $def->getTable() . '.' . $newEntityId
        );

        // Now set the entity that it has been moved to new object table
        $entityDataMapper->setEntityMovedTo($def, $oldEntityId, $newEntityId);
    }
}
