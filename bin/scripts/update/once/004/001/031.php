<?php
/**
 * Move Project Stories to Tasks
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\TaskEntity;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Log\LogFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$log = $serviceManager->get(LogFactory::class);
$serviceManager = $this->account->getServiceManager();

$entityIndex = $serviceManager->get(IndexFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$groupingLoader = $serviceManager->get(GroupingLoaderFactory::class);

// Query all the project story entities
$query = new EntityQuery(ObjectTypes::PROJECT_STORY);
$result = $entityIndex->executeQuery($query);
$num = $result->getTotalNum();

// Loop thru project story and migrate each entity to task
for ($i = 0; $i < $num; $i++) {
    $storyEntity = $result->getEntity($i);
    
    // Create a new task entity
    $newEntity = $entityLoader->create(ObjectTypes::TASK);

    // Import the project story data into the newly created task entity
    $newEntity->fromArray($storyEntity->toArray(), true);

    /*
     * We need to set the value to null so EntityDataMapper can save this entity as a new entity
     * But we will still preserve the guid since they are global now.
     */
    $newEntity->setValue("id", null);
    $newEntity->setValue("start_date", $storyEntity->getValue("date_start"));
    $newEntity->setValue("user_id", $storyEntity->getValue("owner_id"));
    $newEntity->setValue("project", $storyEntity->getValue("project_id"));

    // Make sure that project story's status_id (STATUS_COMPLETED) will mark the task entity as done
    $newEntity->setValue("done", $storyEntity->getValueName("status_id") == TaskEntity::STATUS_COMPLETED);
    
    // Get the task groupings for status_id, priority_id, and type_id
    $statusGroupings = $groupingLoader->get(ObjectTypes::TASK . "/status_id");
    $priorityGroupings = $groupingLoader->get(ObjectTypes::TASK . "/priority_id");
    $typeGroupings = $groupingLoader->get(ObjectTypes::TASK . "/type_id");

    $statusGroup = $statusGroupings->getByName($storyEntity->getValueName("status_id"));
    $priorityGroup = $priorityGroupings->getByName($storyEntity->getValueName("priority_id"));
    $typeGroup = $typeGroupings->getByName($storyEntity->getValueName("type_id"));

    // Set the task's group id and name for status_id, priority_id, and type_id
    $newEntity->setValue("status_id", $statusGroup->id, $statusGroup->name);
    $newEntity->setValue("priority_id", $priorityGroup->id, $priorityGroup->name);
    $newEntity->setValue("type_id", $typeGroup->id, $typeGroup->name);

    if (!$entityDataMapper->save($newEntity)) {
        $errMessage = sprintf("Error saving/moving project story to task. Guid: %s. %s", $newEntity->getValue("guid"), $entityDataMapper->getLastError());
        $log->error($errMessage);
        throw new \RuntimeException($errMessage);
    }

    // Since we are moving the story entity to task and preserving its guid, then we should delete the story entity.
    $entityLoader->delete($storyEntity, true);
    $log->info("Project story successfully moved to task. Guid: " . $newEntity->getValue("guid"));
}