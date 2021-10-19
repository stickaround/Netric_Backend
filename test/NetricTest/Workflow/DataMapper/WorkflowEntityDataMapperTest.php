<?php

declare(strict_types=1);

namespace NetricTest\Workflow\DataMapper;

use Netric\Entity\EntityLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Workflow\DataMapper\WorkflowDataMapperFactory;
use Netric\Workflow\DataMapper\WorkflowEntityDataMapper;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class WorkflowEntityDataMapperTest extends TestCase
{
    /**
     * DataMapper for testing
     *
     * @var WorkflowEntityDataMapper
     */
    private WorkflowEntityDataMapper $dataMapper;

    /**
     * Loader to create/update/load/delete entities
     *
     * @var EntityLoader
     */
    private EntityLoader $entityLoader;

    /**
     * Test user
     *
     * @var UserEntity
     */
    private UserEntity $user;

    /**
     * Array of entities we should delete after each test to keep things clean
     *
     * @var array
     */
    private $entitiesToCleanUp = [];

    /**
     * Setup the real-world datamapper
     *
     * @return void
     */
    protected function setUp(): void
    {
        $account = Bootstrap::getAccount();
        $this->user = Bootstrap::getTestUser();
        $sm = $account->getServiceManager();
        $this->dataMapper = $sm->get(WorkflowDataMapperFactory::class);
        $this->entityLoader = $sm->get(EntityLoaderFactory::class);
    }

    /**
     * Clenaup any test entities
     *
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->entitiesToCleanUp as $ent) {
            $this->entityLoader->delete($ent, $this->user);
        }
    }

    /**
     * Helper used to look for a given workflow in an array of workflows
     *
     * @param string $workflowId
     * @param array $workflows
     * @return bool True if the workflow ID is in the array of workflows
     */
    private function isWorkflowInArray(string $workflowId, array $workflows): bool
    {
        foreach ($workflows as $wf) {
            if ($wf->getEntityId() == $workflowId) {
                return true;
            }
        }

        // Default to false
        return false;
    }

    /**
     * Make sure we can return active workflows based on create|update|delete events
     *
     * @return void
     */
    public function testGetActiveWorkflowsForEvent(): void
    {
        // Create a workflow for a user entity
        $workflow = $this->entityLoader->create(ObjectTypes::WORKFLOW, $this->user->getAccountId());
        $workflow->setValue('f_active', true);
        $workflow->setValue('object_type', ObjectTypes::USER);
        $workflow->setValue('f_on_create', true);
        $workflow->setValue('f_on_update', true);
        $workflow->setValue('f_on_delete', false);
        $workflowId = $this->entityLoader->save($workflow, $this->user);
        $this->entitiesToCleanUp[] = $workflow;

        // Test for create
        $workflows = $this->dataMapper->getActiveWorkflowsForEvent(
            ObjectTypes::USER,
            $this->user->getAccountId(),
            'create'
        );
        $this->assertTrue($this->isWorkflowInArray($workflowId, $workflows));

        // Test for update
        $workflows = $this->dataMapper->getActiveWorkflowsForEvent(
            ObjectTypes::USER,
            $this->user->getAccountId(),
            'update'
        );
        $this->assertTrue($this->isWorkflowInArray($workflowId, $workflows));

        // Make sure it does not return for delete
        $workflows = $this->dataMapper->getActiveWorkflowsForEvent(
            ObjectTypes::USER,
            $this->user->getAccountId(),
            'delete'
        );
        $this->assertFalse($this->isWorkflowInArray($workflowId, $workflows));
    }

    /**
     * Make sure we can get actions
     *
     * @return void
     */
    public function testGetActions(): void
    {
        // Create a workflow for a user entity
        $workflow = $this->entityLoader->create(ObjectTypes::WORKFLOW, $this->user->getAccountId());
        $workflow->setValue('f_active', true);
        $workflow->setValue('object_type', ObjectTypes::USER);
        $workflow->setValue('f_on_create', true);
        $workflowId = $this->entityLoader->save($workflow, $this->user);
        $this->entitiesToCleanUp[] = $workflow;

        // Create a root-level action
        $parentAction = $this->entityLoader->create(ObjectTypes::WORKFLOW_ACTION, $this->user->getAccountId());
        $parentAction->setValue('type_name', 'update_field');
        $parentAction->setValue('workflow_id', $workflowId);
        $parentAction->setValue('data', json_encode(['update_field' => 'name', 'update_value' => 'wf_test']));
        $parentActionId = $this->entityLoader->save($parentAction, $this->user);
        $this->entitiesToCleanUp[] = $parentAction;

        // Create a child action
        $childAction = $this->entityLoader->create(ObjectTypes::WORKFLOW_ACTION, $this->user->getAccountId());
        $childAction->setValue('type_name', 'update_field');
        $childAction->setValue('workflow_id', $workflowId);
        $childAction->setValue('data', json_encode(['update_field' => 'name', 'update_value' => 'wf_test']));
        $childAction->setValue('parent_action_id', $parentActionId);
        $childActionId = $this->entityLoader->save($childAction, $this->user);
        $this->entitiesToCleanUp[] = $childAction;

        // Make sure we can get the root level actions
        $rootActions = $this->dataMapper->getActions($this->user->getAccountId(), $workflowId);
        $this->assertEquals($parentActionId, $rootActions[0]->getEntityId());
        $this->assertEquals(1, count($rootActions));

        // Make sure we can get child actions
        $childActions = $this->dataMapper->getActions($this->user->getAccountId(), $workflowId, $parentActionId);
        $this->assertEquals($childActionId, $childActions[0]->getEntityId());
        $this->assertEquals(1, count($childActions));
    }


    /**
     * Make sure we can create an instance for a workflow:entity relationship
     *
     * @return void
     */
    public function testCreateWorkflowInstance(): void
    {
        // Create a workflow for a user entity
        $workflow = $this->entityLoader->create(ObjectTypes::WORKFLOW, $this->user->getAccountId());
        $workflow->setValue('f_active', true);
        $workflow->setValue('object_type', ObjectTypes::TASK);
        $workflow->setValue('f_on_create', true);
        $workflowId = $this->entityLoader->save($workflow, $this->user);
        $this->entitiesToCleanUp[] = $workflow;

        // Create a simple task to launch the workflow on
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->user->getAccountId());
        $task->setValue('name', 'Workflow Test Task');
        $this->entityLoader->save($task, $this->user);
        $this->entitiesToCleanUp[] = $task;

        $instance = $this->dataMapper->createWorkflowInstance($workflow, $task, $this->user);
        $this->entitiesToCleanUp[] = $instance;
        $this->assertEquals($workflowId, $instance->getValue('workflow_id'));
        $this->assertEquals($task->getEntityId(), $instance->getValue('act_on_entity_id'));
    }

    /**
     * Test that we can get previous workflow instance for an entity
     *
     * @return void
     */
    public function testGetInstancesForEntity(): void
    {
        // Create a workflow for a user entity
        $workflow = $this->entityLoader->create(ObjectTypes::WORKFLOW, $this->user->getAccountId());
        $workflow->setValue('f_active', true);
        $workflow->setValue('object_type', ObjectTypes::TASK);
        $workflow->setValue('f_on_create', true);
        $workflowId = $this->entityLoader->save($workflow, $this->user);
        $this->entitiesToCleanUp[] = $workflow;

        // Create a simple task to launch the workflow on
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->user->getAccountId());
        $task->setValue('name', 'Workflow Test Task');
        $this->entityLoader->save($task, $this->user);
        $this->entitiesToCleanUp[] = $task;

        $instance = $this->dataMapper->createWorkflowInstance($workflow, $task, $this->user);
        $this->entitiesToCleanUp[] = $instance;

        // Get the instances
        $instances = $this->dataMapper->getInstancesForEntity($workflow, $task);
        $this->assertEquals($workflowId, $instances[0]->getValue('workflow_id'));
        $this->assertEquals($task->getEntityId(), $instances[0]->getValue('act_on_entity_id'));
    }
}
