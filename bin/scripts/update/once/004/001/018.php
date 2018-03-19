<?php
/**
 * Move all custom table entities over to objects_* table so that we no longer have to deal with custom tables from entities
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory as EntityDefinitionDataMapperFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery;
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
$entityDefinitionDataMapper = $serviceManager->get(EntityDefinitionDataMapperFactory::class);
$entityIndex = $serviceManager->get(IndexFactory::class);

$objectTypesToMove = array(
    'invoice',
    'discussion',
    'content_feed',
    'content_feed_post',
    'project_milestone',
    'task',
    'calendar_event',
    'report',
    'user',
    'comment',
    'lead',
    'case',
    'project',
    'note',
    'time',
    'product_family',
    'opportunity',
    'product',
    'invoice_template',
    'infocenter_document',
    'calendar_event_proposal',
    'customer',
    'approval',
    'member',
    'sales_order',
    'product_review',
    'dashboard',
    'calendar',
    'workflow',
    'workflow_action',
);

foreach ($objectTypesToMove as $objType) {
    $objectsTable = "objects_$objType";

    $def = null;
    try {
        $entityDefinitionLoader->clearCache($objType);
        $def = $entityDefinitionLoader->get($objType);
    } catch (Exception $ex) {
        $serviceManager->get("Log")->error("Could not load user definition");
        $def = null;
    }

    /*
     * If we dont have an entity definition then we need to create it
     * Or if the object definition table is not yet existing
     * Or if the entity definition is a custom table and do not have an objects_* table
     */
    if (!$def || !$db->tableExists($objectsTable) || ($def && $def->isCustomTable() && $def->getTable() !== $objectsTable)) {

        $entitiesToMove = array();

        $query = new EntityQuery($objType);
        $ret = $entityIndex->executeQuery($query);

        // Get the entities to be move to new table
        for ($i = 0; $i < $ret->getTotalNum(); $i++) {
            $entity = $ret->getEntity($i);
            $entitiesToMove[] = $entity->toArray();
        }

        // Update the object type and clear the object_table so it will use the new objects table
        $def = $entityDefinitionLoader->get($objType);
        $def->object_table = "";

        // Save the entity definition
        $entityDefinitionDataMapper->save($def);

        // Create a new definition table if we do not have it yet
        $entityDefinitionLoader->forceSystemReset($objType);
        $entityDefinitionLoader->clearCache($objType);

        foreach ($entitiesToMove as $entityData) {
            // Create a new entity to save
            $entity = $entityLoader->create($objType);

            // Parse the params of the entity
            $entity->fromArray($entityData);

            /*
             * We need to set the revision value to 0 so the entityDatamapper::save() will do the insert action instead of update
             * If we are not going to set the revision to 0, the entityDatamapper will assume that it will do the update action
             *  since we have an entity id.
             *
             * Please refer to Netric/Entity/EntityDataMapper::saveData() line 287
             */
            $entity->setValue("revision", 0);
            $entityDataMapper->save($entity);

            /*
             * After creating a new entity by setting the revision value to 0
             * Then we need to save the actual revision value of the entity
             */
            $entity->fromArray($entityData);
            $entityDataMapper->save($entity);
        }
    }
}