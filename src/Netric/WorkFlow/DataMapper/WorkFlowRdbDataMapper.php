<?php
namespace Netric\WorkFlow\DataMapper;

use Netric\Entity\EntityLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery;
use Netric\Error\ErrorAwareInterface;
use Netric\Error\Error;
use Netric\WorkFlow\WorkFlow;
use Netric\WorkFlow\WorkFlowInstance;
use Netric\WorkFlow\Action\ActionFactory;
use Netric\WorkFlow\Action\ActionInterface;
use Netric\Db\Relational\RelationalDbInterface;

/**
 * Relational Database datamapper for CRUD operations on a WorkFlow object
 */
class WorkFlowRdbDataMapper extends AbstractDataMapper implements DataMapperInterface
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Action factory needed to construct new WorkFlow objects
     *
     * @var ActionFactory|null
     */
    private $actionFactory = null;

    /**
     * Entity loader for loading up the entity being acted on
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Index used for querying entities - mostly actions
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Construct the WorkFlow DataMapper
     *
     * @param RelationalDbInterface $database Handles to database actions
     * @param EntityLoader $entityLoader Entity loader for loading up the entities
     * @param IndexInterface $entityIndex Index used for querying entities - mostly actions
     * @param ActionFactory $actionFactory Factory to create new actions
     */
    public function __construct(
        RelationalDbInterface $database,
        EntityLoader $entityLoader,
        IndexInterface $entityIndex,
        ActionFactory $actionFactory
    ) {
        $this->database = $database;
        $this->entityLoader = $entityLoader;
        $this->entityIndex = $entityIndex;
        $this->actionFactory = $actionFactory;
    }

    /**
     * Save a new or existing WorkFlow
     *
     * @param WorkFlow $workFlow The workflow to save
     * @return int|null The unique id if success, null on failure (call getLastError for details)
     * @throws \RuntimeException on critical unexpected error
     */
    public function save(WorkFlow $workFlow)
    {
        $data = $workFlow->toArray();

        $workflowEntity = null;
        if ($workFlow->getId()) {
            $workflowEntity = $this->entityLoader->get("workflow", $workFlow->getId());
        } else {
            $workflowEntity = $this->entityLoader->create("workflow");
        }

        // Set entity values
        $workflowEntity->setValue("name", $data['name']);
        $workflowEntity->setValue("notes", $data['notes']);
        $workflowEntity->setValue("object_type", $data['obj_type']);
        $workflowEntity->setValue("f_active", $data['active']);
        $workflowEntity->setValue("f_on_create", $data['on_create']);
        $workflowEntity->setValue("f_on_update", $data['on_update']);
        $workflowEntity->setValue("f_on_delete", $data['on_delete']);
        $workflowEntity->setValue("f_on_daily", $data['on_daily']);
        $workflowEntity->setValue("f_singleton", $data['singleton']);
        $workflowEntity->setValue("f_allow_manual", $data['allow_manual']);
        $workflowEntity->setValue("f_condition_unmet", $data['only_on_conditions_unmet']);
        $workflowEntity->setValue("ts_lastrun", $data['last_run']);

        // Set conditions
        if (count($data['conditions'])) {
            $workflowEntity->setValue("conditions", json_encode($data['conditions']));
        } else {
            $workflowEntity->setValue("conditions", "");
        }

        // Save the entity
        $workflowId = $this->entityLoader->save($workflowEntity);

        // Set the id
        $workFlow->setId($workflowId);

        // Save actions
        $this->saveActions($workFlow->getActions(), $workFlow->getRemovedActions(), $workflowId);

        return $workflowId;
    }

    /**
     * Delete an existing WorkFlow
     *
     * @param WorkFlow $workFlow The workflow to delete
     * @return true on success, false on failure with detauls in getLastError
     */
    public function delete(WorkFlow $workFlow)
    {
        if (!$workFlow->getId()) {
            throw new \InvalidArgumentException("Cannot delete a workflow that has not been saved");
        }

        // Query the workflow action entities so we can delete them
        $query = new EntityQuery("workflow_action");
        $query->andWhere("workflow_id")->equals($workFlow->getId());
        $result = $this->entityIndex->executeQuery($query);

        if (!$result) {
            throw new \RuntimeException("Could not get actions: " . $this->entityIndex->getLastError());
        }

        $num = $result->getNum();

        // Loop thru the result and delete the workflow action entities
        for ($i = 0; $i < $num; $i++) {
            $workflowActionEntity = $result->getEntity($i);
            $this->entityLoader->delete($workflowActionEntity, true);
        }

        // Delete the workflow
        $workflowEntity = $this->entityLoader->get("workflow", $workFlow->getId());
        if ($workflowEntity) {
            $this->entityLoader->delete($workflowEntity, true);
            return true;
        }

        return false;
    }

    /**
     * Open a new workflow by id
     *
     * @param int $workflowId The unique id of the workflow to load
     * @return WorkFlow|null Returns null if $id does not exist
     */
    public function getById($workflowId)
    {
        if (!is_numeric($workflowId)) {
            return null;
        }

        $entityWorkflow = $this->entityLoader->get("workflow", $workflowId);

        if ($entityWorkflow) {
            return $this->constructWorkFlowFromRow($entityWorkflow->toArray());
        }

        return null;
    }

    /**
     * Get a list of WorkFlows as an array
     *
     * @param string $objType If set only get for a specific object type
     * @param bool $onlyActive Only return active workflows, otherwise return all
     * @param string $filterEvent If set, only get workflows listening for a specific event
     * @return WorkFlow[] An array of WorkFlow objects or just an empty array if none found
     * @throws \RuntimeException If the query fails
     */
    public function getWorkFlows($objType = null, $onlyActive = true, $filterEvent = null)
    {
        // Query all actions
        $query = new EntityQuery("workflow");

        if ($onlyActive) {
            $query->andWhere("f_active")->equals(true);
        }

        if ($objType) {
            $query->andWhere("object_type")->equals($objType);
        }

        // Add event filter
        switch ($filterEvent) {
            case 'create':
                $query->andWhere("f_on_create")->equals(true);
                break;
            case 'update':
                $query->andWhere("f_on_update")->equals(true);
                break;
            case 'delete':
                $query->andWhere("f_on_delete")->equals(true);
                break;
            /* Below are for future expansions to periodic workflows
            case 'yearly':
                $sql .= " AND f_on_yearly is TRUE";
                $sql .= " AND (
                            ts_lastrun<='" . date("Y-m-d H:i:s T", strtotime("-1 year")) . "'
                            OR ts_lastrun IS NULL
                          )";
                break;
            case 'monthly':
                $sql .= " AND f_on_monthly is TRUE";
                $sql .= " AND (
                            ts_lastrun<='" . date("Y-m-d H:i:s T", strtotime("-1 month")) . "'
                            OR ts_lastrun IS NULL
                          )";
                break;
            case 'weekly':
                $sql .= " AND f_on_weekly is TRUE";
                $sql .= " AND (
                            ts_lastrun<='" . date("Y-m-d H:i:s T", strtotime("-1 week")) . "'
                            OR ts_lastrun IS NULL
                          )";
                break;
            case 'hourly':
                $sql .= " AND f_on_hourly is TRUE";
                $sql .= " AND (
                            ts_lastrun<='" . date("Y-m-d H:i:s T", strtotime("-1 hour")) . "'
                            OR ts_lastrun IS NULL
                          )";
                break;
            */
            case 'daily':
                $query->andWhere("f_on_daily")->equals(true);
                $query->andWhere("ts_lastrun")->isLessOrEqualTo(date("Y-m-d H:i:s T", strtotime("-1 day")));
                $query->orWhere("ts_lastrun")->equals(null);
                break;
            default:
                // Do nothing
                break;
        }

        $result = $this->entityIndex->executeQuery($query);
        if (!$result) {
            throw new \RuntimeException("Could not get actions: " . $this->entityIndex->getLastError());
        }

        $workFlows = array();
        $num = $result->getNum();
        for ($i = 0; $i < $num; $i++) {
            $entityWorkflow = $result->getEntity($i);

            $workFlows[] = $this->constructWorkFlowFromRow($entityWorkflow->toArray());
        }

        return $workFlows;
    }

    /**
     * Construct a WorkFlow object from a $row from the database
     *
     * @param array $row Associative array of a row of the 'workfows' table
     * @return WorkFlow
     */
    private function constructWorkFlowFromRow($row)
    {
        // Construct to workflow to fill
        $workFlow = new WorkFlow($this->actionFactory);

        // Create data array to import
        $importData = array(
            "id" => $row['id'],
            "name" => $row['name'],
            "obj_type" => $row['object_type'],
            "notes" => $row['notes'],
            "revision" => $row['revision'],
            "active" => $row['f_active'],
            "on_create" => $row['f_on_create'],
            "on_update" => $row['f_on_update'],
            "on_delete" => $row['f_on_delete'],
            "on_daily" => $row['f_on_daily'],
            "singleton" => $row['f_singleton'],
            "allow_manual" => $row['f_allow_manual'],
            "last_run" => $row['ts_lastrun'],
            "only_on_conditions_unmet" => $row['f_condition_unmet'],
            "conditions" => ($row['conditions']) ? json_decode($row['conditions'], true) : null,
            "actions" => $this->getActionsArray($row['id']),
        );

        // Set the data from the row
        $workFlow->fromArray($importData);

        return $workFlow;
    }

    /**
     * Save an instance of a workflow
     *
     * @param WorkFlowInstance $workFlowInstance Instance to save
     * @return int id The unique id of the instance run
     * @throws \InvalidArgumentException if the instance has not been initialized property
     * @throws \RunTimeException If the query fails to save the instance
     */
    public function saveWorkFlowInstance(WorkFlowInstance $workFlowInstance)
    {
        // Make sure the instance is valid
        if (!$workFlowInstance->isValid()) {
            throw new \InvalidArgumentException("Workflow instance has not been set");
        }

        // Setup column values to set
        $workflowData = array(
            "workflow_id" => $workFlowInstance->getWorkFlowId(),
            "object_type_id" => $workFlowInstance->getObjTypeId(),
            "object_type" => $workFlowInstance->getObjType(),
            "object_uid" => $workFlowInstance->getEntityId(),
            "ts_started" => $workFlowInstance->getTimeStarted()->format("Y-m-d H:i:s T"),
            "f_completed" => (($workFlowInstance->isCompleted()) ? 't' : 'f'),
        );

        $workflowInstanceId = $workFlowInstance->getId();
        if ($workflowInstanceId) {
            $this->database->update("workflow_instances", $workflowData, ['id' => $workflowInstanceId]);
        } else {
            $workflowInstanceId = $this->database->insert("workflow_instances", $workflowData);

            // Set the workflow instance id
            if ($workflowInstanceId) {
                $workFlowInstance->setId($workflowInstanceId);
            }
        }

        return $workflowInstanceId;
    }

    /**
     * Get a WorkFlowInstance by id
     *
     * @param int $workFlowInstanceId The unique id of the workflow instance running
     * @return WorkFlowInstance|null
     */
    public function getWorkFlowInstanceById($workFlowInstanceId)
    {
        $sql = "SELECT id, workflow_id, object_type, object_uid, ts_started, f_completed
                FROM workflow_instances WHERE id=:id";

        $result = $this->database->query($sql, ["id" => $workFlowInstanceId]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            $entity = $this->entityLoader->get($row['object_type'], $row['object_uid']);

            // Entity was deleted
            if (!$entity) {
                return null;
            }

            $workFlowInstance = new WorkFlowInstance($row['workflow_id'], $entity, $row['id']);
            $workFlowInstance->setTimeStarted(new \DateTime($row['ts_started']));
            $workFlowInstance->setCompleted(($row['f_completed'] == 1) ? true : false);
            return $workFlowInstance;
        }

        // Assume failure
        return null;
    }

    /**
     * Delete a workflow instance id
     *
     * This is only for admin really because an instance will almost always be set to completed
     * but never deleted since we want to maintain a record of the instance run.
     *
     * @param int $WorkFlowInstanceId The workflow instance id that we are going to delete
     * @throws \InvalidArgumentException if anything but a workFlowInstanceId is passed
     */
    public function deleteWorkFlowInstance($workFlowInstanceId)
    {
        if (!is_numeric($workFlowInstanceId)) {
            throw new \InvalidArgumentException("Only a valid WorkFlowInstance id must be passed");
        }

        $this->database->delete(
            'workflow_instances',
            ['id' => $workFlowInstanceId]
        );
    }

    /**
     * Get conditions array for a workflow
     *
     * @param int $workflowId Unique id of the workflow to get actions for
     * @param int $parentActionid Get child actions for a parent action
     * @param array $circularCheck Log of previously added actions to avoid circular references
     * @return array
     */
    private function getActionsArray($workflowId, $parentActionId = null, $circularCheck = array())
    {
        if (!is_numeric($workflowId) && !is_numeric($parentActionId)) {
            throw new \InvalidArgumentException("A valid workflow id or parent action id must be passed");
        }

        $actionsArray = array();

        // Query all actions
        $query = new EntityQuery("workflow_action");
        if ($parentActionId) {
            $query->where("parent_action_id")->equals($parentActionId);
        } else {
            $query->where("parent_action_id")->equals("");
            $query->andWhere("workflow_id")->equals($workflowId);
        }

        $result = $this->entityIndex->executeQuery($query);
        if (!$result) {
            throw new \RuntimeException("Could not get actions: " . $this->entityIndex->getLastError());
        }

        $num = $result->getNum();
        for ($i = 0; $i < $num; $i++) {
            $action = $result->getEntity($i);

            /*
             * Actions can be children of other actions.
             * Check to make sure there are no circular relationships where a child
             * lists a parent as it's own child - that would be very bad!
             */
            if (in_array($action->getId(), $circularCheck)) {
                throw new \RunTimeException($action->getId() . " is a curcular dependency because it was already added");
            } else {
                $circularCheck[] = $action->getId();
            }

            // If type is not defined then throw an exception since it is required
            if (!$action->getValue("type_name")) {
                throw new \RuntimeException("Action " . $action->getId() . " does not have a type_name set");
            }

            $actionArray = array(
                "id" => $action->getId(),
                "name" => $action->getValue("name"),
                "workflow_id" => $action->getValue("workflow_id"),
                "type" => $action->getValue("type_name"),
                "parent_action_id" => $action->getValue("parent_action_id"),
                "actions" => $this->getActionsArray($action->getValue("workflow_id"), $action->getId(), $circularCheck),
            );

            if ($action->getValue("data")) {
                $actionArray['params'] = json_decode($action->getValue("data"), true);
            }

            $actionsArray[] = $actionArray;
        }

        return $actionsArray;
    }

    /**
     * Save actions for a workflow or a parent action
     *
     * @param ActionInterface[] $actionsToAdd
     * @param ActionInterface[] $actionsToRemove
     * @param int $workflowId
     * @param int $parentActionId
     */
    private function saveActions(array $actionsToAdd, array $actionsToRemove, $workflowId, $parentActionId = null)
    {
        if (!is_numeric($workflowId) && !is_numeric($parentActionId)) {
            throw new \InvalidArgumentException("Must pass either workflowId or parantActionId as params");
        }

        // First purge any actions queued to be deleted
        foreach ($actionsToRemove as $action) {
            $actionEntity = $this->entityLoader->get("workflow_action", $action->getId());
            if (!$this->entityLoader->delete($actionEntity, true)) {
                throw new \RuntimeException("Could not delete action");
            }
        }

        foreach ($actionsToAdd as $action) {
            $this->saveAction($action, $workflowId, $parentActionId);
        }
    }

    /**
     * Save an individual action
     *
     * @param ActionInterface $actionToSave
     * @param int $workflowId
     * @param int $parentActionId
     * @return bool true on success, false on failure
     */
    private function saveAction(ActionInterface $actionToSave, $workflowId, $parentActionId = null)
    {
        $actionData = $actionToSave->toArray();

        if (!isset($actionData['type']) || !$actionData['type']) {
            throw new \InvalidArgumentException("Type is required but not set in: " . var_export($actionData, true));
        }

        $actionEntity = $this->entityLoader->create("workflow_action");
        $actionEntity->setValue("type", 0); // for legacy code - can eventually delete when /lib/Workflow is deleted
        $actionEntity->setValue("type_name", $actionData['type']);
        $actionEntity->setValue("name", $actionData['name']);
        $actionEntity->setValue("workflow_id", $workflowId);
        $actionEntity->setValue("parent_action_id", $parentActionId);
        $actionEntity->setValue("data", json_encode($actionData['params']));

        if (!$this->entityLoader->save($actionEntity)) {
            throw new \RuntimeException("Could not save action");
        }

        $actionToSave->setId($actionEntity->getId());

        // Save child actions
        $this->saveActions(
            $actionToSave->getActions(),
            $actionToSave->getRemovedActions(),
            $workflowId,
            $actionToSave->getId()
        );

        return false;
    }

    /**
     * Schedule an action to run at some time in the future
     *
     * @param int $workFlowInstanceId
     * @param int $actionId
     * @param \DateTime $executeTime
     * @return bool true on success, false on failure
     */
    public function scheduleAction($workFlowInstanceId, $actionId, \DateTime $executeTime)
    {
        if (!is_numeric($workFlowInstanceId) || !is_numeric($actionId)) {
            throw new \InvalidArgumentException("The first two params must be numeric");
        }

        $scheduleData = [
            "action_id" => $actionId,
            "ts_execute" => $executeTime->format("Y-m-d g:i a T"),
            "instance_id" => $workFlowInstanceId
        ];

        $scheduleId = $this->database->insert("workflow_action_schedule", $scheduleData);

        return ($scheduleId) ? true : false;
    }

    /**
     * Delete a scheduled action if set for a workflow instance and an action
     *
     * @param int $workFlowInstanceId
     * @param int $actionId
     * @return bool true on success, false on failure
     */
    public function deleteScheduledAction($workFlowInstanceId, $actionId)
    {
        if (!is_numeric($workFlowInstanceId) || !is_numeric($actionId)) {
            throw new \InvalidArgumentException("The first two params must be numeric");
        }

        $this->database->delete(
            'workflow_action_schedule',
            ['action_id' => $actionId, "instance_id" => $workFlowInstanceId]
        );

        return true;
    }

    /**
     * Get a scheduled action time if set for a workflow instance and an action
     *
     * @param int $workFlowInstanceId
     * @param int $actionId
     * @return \DateTime|null
     */
    public function getScheduledActionTime($workFlowInstanceId, $actionId)
    {
        if (!is_numeric($workFlowInstanceId) || !is_numeric($actionId)) {
            throw new \InvalidArgumentException("The first two params must be numeric");
        }

        $sql = "SELECT ts_execute FROM workflow_action_schedule
                WHERE action_id=:action_id AND instance_id=:instance_id";

        $result = $this->database->query($sql, ["action_id" => $actionId, "instance_id" => $workFlowInstanceId]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            $strTime = $row["ts_execute"];

            // $strTime should always be set, but you can never be too careful
            if (!$strTime) {
                return null;
            }

            // We should have a valid time from the PGSQL timestamp column, return the new date
            return new \DateTime($strTime);
        }

        // Action is not scheduled
        return null;
    }

    /**
     * Get all actions scheduled to be executed on or before $toDate
     *
     * @param \DateTime $toDate
     * @return array(array("instance"=>WorkFlowInstance, "action"=>ActionInterface))
     */
    public function getScheduledActions(\DateTime $toDate = null)
    {
        // Return array
        $actions = array();

        // If no date was passed use now
        if ($toDate === null) {
            $toDate = new \DateTime();
        }

        $sql = "SELECT action_id, instance_id FROM workflow_action_schedule
                WHERE ts_execute<=:ts_execute";

        $result = $this->database->query($sql, ["ts_execute" => $toDate->format("Y-m-d g:i a T")]);

        // Get all scheduled actions
        foreach ($result->fetchAll() as $row) {
            $instance = $this->getWorkFlowInstanceById($row['instance_id']);
            $action = $this->getActionById($row['action_id']);

            // Only return the scheduled action if the instance and action are still valid
            if ($instance && $action) {
                $actions[] = array(
                    "instance" => $instance,
                    "action" => $action,
                );
            } else {
                // It looks like either the action was deleted or the instance was cancelled, cleanup
                $this->deleteScheduledAction($row['instance_id'], $row['action_id']);
            }
        }

        return $actions;
    }

    /**
     * Load up an action by id
     *
     * This is not a public function, it is used for internal functions only right now
     *
     * @param $actionId
     * @return ActionInterface
     */
    private function getActionById($actionId)
    {
        if (!$actionId || !is_numeric($actionId)) {
            throw new \InvalidArgumentException("First param is required to load an action");
        }

        $sql = "SELECT * FROM objects_workflow_action WHERE id=:id";
        $result = $this->database->query($sql, ["id" => $actionId]);

        if ($result->rowCount()) {
            $row = $result->fetch();

            $actionArray = array(
                "id" => $row['id'],
                "name" => $row['name'],
                "workflow_id" => $row['workflow_id'],
                "type" => $row['type_name'],
                "parent_action_id" => $row['parent_action_id'],
                "child_actions" => $this->getActionsArray($row['workflow_id'], $actionId),
            );

            // TODO: get child actions

            // Get params
            if ($row['data']) {
                $actionArray['params'] = json_decode($row['data'], true);
            }

            // Create action from data
            $action = $this->actionFactory->create($actionArray['type']);
            $action->fromArray($actionArray);
            return $action;
        }

        // Not found
        return null;
    }
}
