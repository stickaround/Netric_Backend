<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\Field;
use Netric\EntityQuery\Index\IndexInterface;
use Ramsey\Uuid\Uuid;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoader;
use RuntimeException;

/**
 * Initializer to make sure accounts have a default set of groupings
 */
class WorkflowsInitData implements InitDataInterface
{
    /**
     * List of worfklows to create
     */
    private array $workflowsData = [];

    /**
     * Index used to query entities
     */
    private IndexInterface $entityIndex;

    /**
     * Entity loader
     */
    private Entityloader $entityLoader;

    /**
     * Used to get actual grouping IDs from names
     *
     * @var GroupingLoader
     */
    private GroupingLoader $groupingLoader;

    /**
     * Constructor
     *
     * @param array $workflowsData
     */
    public function __construct(
        array $workflowsData,
        IndexInterface $entityIndex,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader
    ) {
        $this->workflowsData = $workflowsData;
        $this->entityIndex = $entityIndex;
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
    }

    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool
    {
        foreach ($this->workflowsData as $workflowData) {
            // Get the existing workflow by uname
            $workflow = $this->entityLoader->getByUniqueName(
                ObjectTypes::WORKFLOW,
                $workflowData['uname'],
                $account->getAccountId()
            );

            // If it does not already exist, then create it
            if (!$workflow) {
                $workflow = $this->entityLoader->create(ObjectTypes::WORKFLOW, $account->getAccountId());
            }

            // Set fields from data array and save
            // second param will only update provided fields so we don't overwrite entity_id and such
            $workflow->fromArray($workflowData, true);
            $workflowId = $this->entityLoader->save($workflow, $account->getSystemUser());

            // Now save actions
            if (isset($workflowData['child_actions'])) {
                $this->saveActions($account, $workflowId, $workflowData['object_type'], $workflowData['child_actions']);
            }
        }

        return true;
    }

    /**
     * Save list of actions (and child-actions if defined)
     *
     * @param Account $account
     * @param string $workflowId
     * @param string $objectType
     * @param array $actions
     * @return void
     */
    private function saveActions(Account $account, string $workflowId, string $objectType, array $actions, string $parentId = '')
    {
        foreach ($actions as $actionData) {
            // Get the existing action by uname
            $action = $this->entityLoader->getByUniqueName(
                ObjectTypes::WORKFLOW_ACTION,
                $actionData['uname'],
                $account->getAccountId()
            );

            // If it does not already exist, then create it
            if (!$action) {
                $action = $this->entityLoader->create(ObjectTypes::WORKFLOW_ACTION, $account->getAccountId());
            }

            // Sanitize data - like grouping names-to-IDs - and encode json
            $actionData['data'] = $this->sanitizeAndEncodeParams($account, $objectType, $actionData);

            // Set fields from data array and save
            // second param will only update provided fields so we don't overwrite entity_id and such
            $action->fromArray($actionData, true);

            // Set the workflow id
            $action->setValue('workflow_id', $workflowId);

            // Set parentId
            if ($parentId) {
                $action->setValue('parent_action_id', $parentId);
            }

            $actionId = $this->entityLoader->save($action, $account->getSystemUser());

            // Check for child actions
            if (isset($actionData['child_actions'])) {
                $this->saveActions($account, $workflowId, $objectType, $actionData['child_actions'], $actionId);
            }
        }
    }

    /**
     * Sanitize data fields - like changing grouping names to IDs
     *
     * @param Account $account
     * @param string $objectType
     * @param array $data
     * @return string JSON encoded params
     */
    private function sanitizeAndEncodeParams(Account $account, string $objectType, array $actionData): string
    {
        $params = $actionData['data'];

        if ($actionData['type_name'] === 'check_condition') {
            $definition = $this->entityLoader->getEntityDefinitionByName(
                $objectType,
                $account->getAccountId()
            );
            for ($i = 0; $i < count($params['conditions']); $i++) {
                $condition = $params['conditions'][$i];
                $field = $definition->getField($condition['field_name']);
                if (!$field) {
                    throw new RuntimeException("Field " . $condition['field_name'] . " not found");
                }

                // Replace group names with UUIDs
                if ($field->type === Field::TYPE_GROUPING && !Uuid::isValid($condition['value'])) {
                    $groupings = $this->groupingLoader->get(
                        $objectType . "/" . $field->name,
                        $account->getAccountId()
                    );

                    $group = $groupings->getByName($condition['value']);
                    if ($group) {
                        // Replace the name with the ID of the grouping
                        $params['conditions'][$i]['value'] = $group->getGroupId();
                    }
                }
            }
        }

        return json_encode($params);
    }
}
