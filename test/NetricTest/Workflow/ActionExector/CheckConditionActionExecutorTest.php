<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityQuery\Where;
use Netric\Workflow\ActionExecutor\ActionExecutorInterface;
use Netric\Workflow\ActionExecutor\CheckConditionActionExecutor;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\Results;

/**
 * Test action executor
 */
class CheckConditionActionExecutorTest extends TestCase
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
    private IndexInterface $mockEntityIndex;
    /**
     * Mock and stub out the action exector
     */
    protected function setUp(): void
    {
        $this->mockActionEntity = $this->createMock(WorkflowActionEntity::class);
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockEntityIndex = $this->createMock(IndexInterface::class);
        $this->executor = new CheckConditionActionExecutor(
            $this->mockEntityLoader,
            $this->mockActionEntity,
            'http://mockhost',
            $this->mockEntityIndex
        );
    }

    /**
     * Make sure we can update a basic field
     */
    public function testExecute(): void
    {
        // Set the entity action data
        $this->mockActionEntity->method("getData")->willReturn([
            'conditions' => [
                ['blogic' => Where::COMBINED_BY_AND, 'field_name' => 'first_name', 'operator' => Where::OP_EQ, 'value' => 'Sky']
            ]
        ]);

        // Create test entity and a mock entity definition that is returned
        // and use in the execute function to construct a query
        $testEntity = $this->createMock(EntityInterface::class);
        $testEntity->method('getEntityId')->willReturn('UUID-SAVED');
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $mockEntityDefinition->method('getObjType')->willReturn('user');
        $testEntity->method("getDefinition")->willReturn($mockEntityDefinition);

        // Fake results
        $mockResults = $this->createMock(Results::class);
        $mockResults->method('getNum')->willReturn(1);
        $this->mockEntityIndex->method('executeQuery')->willReturn($mockResults);

        // TODO: We need to find a test to test the actual query

        // Stub the user to satisfy requirements for call to execute
        $user = $this->createMock(UserEntity::class);
        $user->method('getAccountId')->willReturn('UUID-ACCOUNT-ID');

        // Execute
        $this->assertTrue($this->executor->execute($testEntity, $user));
    }
}
