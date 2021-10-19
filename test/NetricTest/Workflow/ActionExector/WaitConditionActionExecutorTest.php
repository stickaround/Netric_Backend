<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Workflow\ActionExecutor\ActionExecutorInterface;
use Netric\Workflow\ActionExecutor\WaitConditionActionExecutor;
use Netric\WorkerMan\SchedulerService;
use Netric\Workflow\WorkflowScheudleTimes;

/**
 * Test action executor
 */
class WaitConditionActionExecutorTest extends TestCase
{
    /**
     * Executor to test (not a mock of course)
     */
    private ActionExecutorInterface $executor;

    /**
     * mock dependencies
     */
    private EntityLoader $mockEntityLoader;
    private WorkflowActionEntity $mockActionEntity;
    private SchedulerService $mockScheduler;

    /**
     * Mock and stub out the action exector
     */
    protected function setUp(): void
    {
        $this->mockActionEntity = $this->createMock(WorkflowActionEntity::class);
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockScheduler = $this->createMock(SchedulerService::class);
        $this->executor = new WaitConditionActionExecutor(
            $this->mockEntityLoader,
            $this->mockActionEntity,
            'http://mockhost',
            $this->mockScheduler
        );
    }

    /**
     * Make sure we can schedule a job to run after a certain amount of time
     */
    public function testExecute(): void
    {
        // Set the entity action to run in 1 day
        $this->mockActionEntity->method("getData")->willReturn([
            'when_interval' => 1,
            'when_unit' => WorkflowScheudleTimes::TIME_UNIT_DAY,
        ]);

        // Create test entity and a mock entity definition that is returned
        // and use in the execute function to construct a query
        $testEntity = $this->createMock(EntityInterface::class);
        $testEntity->method('getEntityId')->willReturn('UUID-SAVED');
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $mockEntityDefinition->method('getObjType')->willReturn('user');
        $testEntity->method("getDefinition")->willReturn($mockEntityDefinition);

        // Stub the user to satisfy requirements for call to execute
        $user = $this->createMock(UserEntity::class);
        $user->method('getAccountId')->willReturn('UUID-ACCOUNT-ID');
        $user->method('getEntityId')->willReturn('UUID-USER-ID');

        // Make sure we called scheudleAtTime
        $this->mockScheduler->expects($this->once())->method('scheduleAtTime');

        // Execution should return false which pauses the workflow
        $this->assertFalse($this->executor->execute($testEntity, $user));
    }
}
