<?php
/**
 * Cleanup contacts that are missing a commit_id
 *
 * Contacts that were created before we started saving global
 * commit_id for each entity would never be synchronized with external
 * devices - such as ActiveSync (z-push).
 *
 * This script will simply find and re-save any contacts missing a commit_id
 * so that synchronization will be fixed.
 */
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery;


$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$index = $serviceManager->get(IndexFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

$def = null;
try {
    $def = $entityDefinitionLoader->get(ObjectTypes::CONTACT_PERSONAL);
} catch (Exception $ex) {
    $serviceManager->get("Log")->error("Could not load contact_personal definition");
    $def = null;
}

// Make sure that we have contact_personal entities
if ($def) {

    // Find all contact_personal entities where commit_id is null
    $query = new EntityQuery(ObjectTypes::CONTACT_PERSONAL);
    $query->where("commit_id")->equals("");

    // Get the results
    $results = $index->executeQuery($query);
    $totalNum = $results->getTotalNum();

    // Loop over total num - the results will paginate as needed
    for ($i = 0; $i < $totalNum; $i++) {

        // Get each contact
        $entity = $results->getEntity($i);

        // Just saving the entity will result in a new commit id being created
        if ($entity) {
            $entityLoader->save($entity);
        }
    }
}

