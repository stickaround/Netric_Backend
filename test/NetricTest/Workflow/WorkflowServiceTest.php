<?php

declare(strict_types=1);

namespace NetricTest\Workflow;

use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Entity\ObjType\WorkflowEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Log\LogInterface;
use Netric\Workflow\ActionExecutor\ActionExecutorInterface;
use Netric\Workflow\ActionExecutorFactory;
use Netric\Workflow\DataMapper\WorkflowDataMapperInterface;
use Netric\Workflow\WorkflowService;
use PHPUnit\Framework\TestCase;

/**
 * Test action executor
 */
class WorkflowServiceTest extends TestCase
{
    /**
     * Mock dependencies
     */
    private WorkflowDataMapperInterface $mockDataMapper;
    private ActionExecutorFactory $mockActionExecutorFactory;
    private EntityInterface $mockTestEntity;
    private UserEntity $mockUser;

    /**
     * System under test
     */
    private WorkflowService $workflowService;

    /**
     * Mock out the service dependencies to make tests easier
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mockDataMapper = $this->createMock(WorkflowDataMapperInterface::class);
        $this->mockActionExecutorFactory = $this->createMock(ActionExecutorFactory::class);
        $this->workflowService = new WorkflowService(
            $this->mockDataMapper,
            $this->mockActionExecutorFactory,
            $this->createMock(LogInterface::class)
        );

        // Create test entity and a mock entity definition
        $this->mockTestEntity = $this->createMock(EntityInterface::class);
        $this->mockTestEntity->method('getEntityId')->willReturn('UUID-ENT-1');
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $mockEntityDefinition->method('getObjType')->willReturn('task');
        $this->mockTestEntity->method("getDefinition")->willReturn($mockEntityDefinition);

        // Mock out a test user entity
        $this->mockUser = $this->createMock(UserEntity::class);
        $this->mockUser->method('getAccountId')->willReturn('UUID-ACCOUNT-ID');
        $this->mockUser->method('getEntityId')->willReturn('UUID-USER-ID');
    }

    /**
     * Make sure we can run a simple workflow
     *
     * @return void
     */
    public function testRunWorkflowsOnEvent(): void
    {
        // Create a mock workflow to work with
        $mockWorkflow = $this->createMock(WorkflowEntity::class);
        $mockWorkflow->method('getEntityId')->willReturn('UUID-WORKFLOW');
        $mockWorkflow->method('getValue')->with($this->equalTo('f_singleton'))->willReturn(false);

        // Create a mock action to add to the worfklow
        $mockTestAction = $this->createMock(WorkflowActionEntity::class);
        $mockTestAction->method('getValue')->with($this->equalTo('workflow_id'))->willReturn('UUID-WORKFLOW');
        $mockTestAction->method('getEntityId')->willReturn('UUID-ACTION');

        /*
         * Mock 2 calls to datamapper::getActions that will be called in the service.
         * The first call will be with no parentAction to get all root level actions
         * for the workflow, the second call will come later to check if there are any
         * child actions of each root action.
         */
        $this->mockDataMapper->method('getActions')->will($this->returnValueMap([
            ['UUID-ACCOUNT-ID', 'UUID-WORKFLOW', '', [$mockTestAction]],
            ['UUID-ACCOUNT-ID', 'UUID-WORKFLOW', 'UUID-ACTION', []]
        ]));


        // Now test the serivce
        // ----------------

        // 1: First the service gets any workflows for this event - return the mock
        $this->mockDataMapper
            ->method('getActiveWorkflowsForEvent')
            ->with(
                $this->equalTo('task'),
                $this->equalTo('UUID-ACCOUNT-ID'),
                $this->equalTo('create')
            )
            ->willReturn([$mockWorkflow]);

        // 2: The service will create an instance for the workflow to protect
        // against duplicate execution if this is a singleton
        $mockInstance = $this->createMock(EntityInterface::class);
        $this->mockDataMapper->method('createWorkflowInstance')->with(
            $this->equalTo($mockWorkflow),
            $this->equalTo($this->mockTestEntity),
            $this->equalTo($this->mockUser)
        )->willReturn($mockInstance);

        // 3: Return a mock executor that will just return true on execution
        $mockActionExecutor = $this->createMock(ActionExecutorInterface::class);
        $mockActionExecutor->method('execute')->willReturn(true);
        $this->mockActionExecutorFactory->method('create')->willReturn($mockActionExecutor);

        // Make sure execute get's called for the action
        $mockActionExecutor->expects($this->once())->method('execute');
        $this->workflowService->runWorkflowsOnEvent($this->mockTestEntity, 'create', $this->mockUser);
    }

    /**
     * Make sure we cannot duplicate execution on a singleton workflow
     *
     * @return void
     */
    public function testRunWorkflowsOnEventSingleton(): void
    {
        // Create a mock workflow to work with
        $mockWorkflow = $this->createMock(WorkflowEntity::class);
        $mockWorkflow->method('getEntityId')->willReturn('UUID-WORKFLOW');
        $mockWorkflow->method('getValue')->with($this->equalTo('f_singleton'))->willReturn(true);

        // Mock returning a previous instance - should just cause the process to exit
        $mockInstance = $this->createMock(EntityInterface::class);
        $this->mockDataMapper->method('getInstancesForEntity')->with(
            $this->equalTo($mockWorkflow),
            $this->equalTo($this->mockTestEntity),
        )->willReturn([$mockInstance]);

        // Make sure that createWorkflowInstance is never called
        $this->mockDataMapper->expects($this->never())->method('createWorkflowInstance');

        // Run the process which should end pretty quickly
        $this->workflowService->runWorkflowsOnEvent($this->mockTestEntity, 'create', $this->mockUser);
    }

    /**
     * Make sure we can continue execution of a parent action
     *
     * This is currently only used for WaitCondition actions to resume execution
     * of a workflow by running all child actions.
     *
     * @return void
     */
    public function testRunChildActions(): void
    {
        // Create a mock workflow to work with
        $mockWorkflow = $this->createMock(WorkflowEntity::class);
        $mockWorkflow->method('getEntityId')->willReturn('UUID-WORKFLOW');
        $mockWorkflow->method('getValue')->with($this->equalTo('f_singleton'))->willReturn(false);

        // Create a mock actions to add to the worfklow
        $mockParentAction = $this->createMock(WorkflowActionEntity::class);
        $mockParentAction->method('getValue')->with($this->equalTo('workflow_id'))->willReturn('UUID-WORKFLOW');
        $mockParentAction->method('getEntityId')->willReturn('UUID-PARENT-ACTION');

        $mockChildAction = $this->createMock(WorkflowActionEntity::class);
        $mockChildAction->method('getValue')->with($this->equalTo('workflow_id'))->willReturn('UUID-WORKFLOW');
        $mockChildAction->method('getEntityId')->willReturn('UUID-CHILD-ACTION');

        // Get child actions
        $this->mockDataMapper->method('getActions')->will($this->returnValueMap([
            // The parent action has one child - parent is probably a wait condition
            ['UUID-ACCOUNT-ID', 'UUID-WORKFLOW', 'UUID-PARENT-ACTION', [$mockChildAction]],
            // Child has no children
            ['UUID-ACCOUNT-ID', 'UUID-WORKFLOW', 'UUID-CHILD-ACTION', []]
        ]));

        // Return a mock action executor that will just return true on execution
        $mockActionExecutor = $this->createMock(ActionExecutorInterface::class);
        $mockActionExecutor->method('execute')->willReturn(true);
        $this->mockActionExecutorFactory->method('create')->willReturn($mockActionExecutor);

        // Make sure execute get's called for the child action
        $mockActionExecutor->expects($this->once())->method('execute');
        $this->workflowService->runChildActions($mockParentAction, $this->mockTestEntity, $this->mockUser);
    }
}
