<?php
/**
 * Move Project Stories to Tasks
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\TaskEntity;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Log\LogFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$log = $serviceManager->get(LogFactory::class);

$entityIndex = $serviceManager->get(IndexFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$groupingLoader = $serviceManager->get(GroupingLoaderFactory::class);
$rdb = $serviceManager->get(RelationalDbFactory::class);

// Make sure that the project story table still exists
$projectStoryTableName = "objects_project_story";
if (!$rdb->tableExists($projectStoryTableName)) {
    $log->warning("Update004001031:: Project story table is not available anymore");
    return;
}

$result = $rdb->query("SELECT * FROM $projectStoryTableName as project_story
                        WHERE NOT EXISTS (SELECT * FROM objects_task WHERE field_data->>'guid' = project_story.field_data->>'guid')");
foreach ($result->fetchAll() as $storyRawData) {
    $storyData = json_decode($storyRawData['field_data'], true);

    // Create a new task entity
    $newEntity = $entityLoader->create(ObjectTypes::TASK);

    // This will import the story data that both project story and task have in common.
    $newEntity->fromArray($storyData, true);

    /*
     * We need to set the value to null so EntityDataMapper can save this entity as a new entity
     * But we will still preserve the guid since they are global now.
     */
    $newEntity->setValue("id", null);

    // Manually set the task data here, since these are the fields that were not captured when importing the story data.
    $newEntity->setValue("start_date", $storyData["date_start"]);
    $newEntity->setValue("user_id", $storyData["owner_id"]);
    $newEntity->setValue("project", $storyData["project_id"]);

    // Get the groupings name from the *_fval
    $statusIdName = $storyData["status_id_fval"][$storyData["status_id"]];
    $priorityIdName = $storyData["priority_id_fval"][$storyData["priority_id"]];
    $typeIdName = $storyData["type_id_fval"][$storyData["type_id"]];

    // Make sure that project story's status_id (STATUS_COMPLETED) will mark the task entity as done
    $newEntity->setValue("done", $statusIdName == TaskEntity::STATUS_COMPLETED);
    
    // Get the task groupings for status_id, priority_id, and type_id
    $statusGroupings = $groupingLoader->get(ObjectTypes::TASK . "/status_id");
    $priorityGroupings = $groupingLoader->get(ObjectTypes::TASK . "/priority_id");
    $typeGroupings = $groupingLoader->get(ObjectTypes::TASK . "/type_id");

    // Since Project Story's status 'todo' is labeled as 'Todo' while task entity is 'ToDo'
    if (strtolower($statusIdName) == "todo") {
        $statusIdName = TaskEntity::STATUS_TODO;
    }

    $statusGroup = $statusGroupings->getByName($statusIdName);
    $priorityGroup = $priorityGroupings->getByName($priorityIdName);
    $typeGroup = $typeGroupings->getByName($typeIdName);

    // Set the task's group id and name for status_id, priority_id, and type_id
    $newEntity->setValue("status_id", $statusGroup->id, $statusGroup->name);
    $newEntity->setValue("priority_id", $priorityGroup->id, $priorityGroup->name);
    $newEntity->setValue("type_id", $typeGroup->id, $typeGroup->name);

    if (!$entityDataMapper->save($newEntity)) {
        $errMessage = sprintf("Update004001031:: Error saving/moving project story to task. Guid: %s. %s", $newEntity->getGuid(), $entityDataMapper->getLastError());
        $log->error($errMessage);
        throw new \RuntimeException($errMessage);
    }

    // Query all the project story's comments
    $query = new EntityQuery(ObjectTypes::COMMENT);
    $query->where('obj_reference')->equals("project_story:{$storyData['id']}");
    $commentsResult = $entityIndex->executeQuery($query);

    // Loop thru each comment entity and update its object reference to the new task entity
    for ($i = 0; $i < $commentsResult->getTotalNum(); $i++) {
        $commentEntity = $commentsResult->getEntity($i);
        $commentEntity->setValue("obj_reference", ObjectTypes::TASK . ":{$newEntity->getId()}", $newEntity->getName());
        $entityDataMapper->save($commentEntity);
    }

    // Since we are moving the story entity to task and preserving its guid, then we should delete the story entity.
    $result = $rdb->query("DELETE FROM $projectStoryTableName WHERE id = :id", ["id" => $storyData["id"]]);
    $log->info("Update004001031:: Project story successfully moved to task. Guid: " . $newEntity->getGuid());
}