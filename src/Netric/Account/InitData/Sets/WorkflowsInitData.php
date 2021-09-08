<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\WorkerMan\SchedulerService;
use Netric\EntityDefinition\ObjectTypes;

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
     * Scheudler used to scheudle recurring jobs
     */
    private SchedulerService $schedulerService;

    /**
     * Entity loader
     */
    private Entityloader $entityLoader;

    /**
     * Constructor
     *
     * @param array $workflowsData
     */
    public function __construct(
        array $workflowsData,
        IndexInterface $entityIndex,
        SchedulerService $schedulerService,
        EntityLoader $entityLoader
    ) {
        $this->workflowsData = $workflowsData;
        $this->entityIndex = $entityIndex;
        $this->schedulerService = $schedulerService;
        $this->entityLoader = $entityLoader;
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
                $workflowData['name'],
                $account->getAccountId()
            );

            // If it does not already exist, then create it
            if (!$workflow) {
                $workflow = $this->entityLoader->create(ObjectTypes::WORKFLOW, $account->getAccountId());
            }

            // Set fields from data array and save
            $workflow->fromArray($workflowData);
            $workflowId = $this->entityLoader->save($workflow, $account->getSystemUser());

            // Now save actions
            if (isset($workflowData['child_actions'])) {
                $this->saveActions($account, $workflowId, $workflowData['child_actions']);
            }
        }

        return true;
    }

    /**
     * Save list of actions (and child-actions if defined)
     *
     * @param Account $account
     * @param string $workflowId
     * @param array $actions
     * @return void
     */
    private function saveActions(Account $account, string $workflowId, array $actions, string $parentId = '')
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

            // Set fields from data array and save
            $action->fromArray($actionData);

            // Set parentId
            $action->setValue('parent_action_id', $parentId);

            $actionId = $this->entityLoader->save($action, $account->getSystemUser());

            // Check for child actions
            if (isset($actionData['child_actions'])) {
                $this->saveActions($account, $workflowId, $actionData['child_actions'], $actionId);
            }
        }
    }
}
