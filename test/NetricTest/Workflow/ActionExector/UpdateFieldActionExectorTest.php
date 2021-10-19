<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\Workflow\ActionExecutor\ActionExecutorInterface;
use Netric\Workflow\ActionExecutor\UpdateFieldActionExecutor;

/**
 * Test action executor
 */
class UpdateFieldActionExectorTest extends TestCase
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

    /**
     * Mock and stub out the action exector
     */
    protected function setUp(): void
    {
        $this->mockActionEntity = $this->createMock(WorkflowActionEntity::class);
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->executor = new UpdateFieldActionExecutor(
            $this->mockEntityLoader,
            $this->mockActionEntity,
            'http://mockhost'
        );
    }

    /**
     * Make sure we can update a basic field
     */
    public function testExecute(): void
    {
        // Set the entity action data
        $this->mockActionEntity->method("getData")->willReturn([
            'update_field' => 'name',
            'update_value' => 'edited test'
        ]);

        // Create a mock test entity, with a mock definition that gets a field
        // This is important because the execute function will make sure the field
        // exists and get the type of data from the field definition
        $testEntity = $this->createMock(EntityInterface::class);
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $mockEntityDefinition->method('getField')->willReturn(new Field());
        $testEntity->method("getDefinition")->willReturn($mockEntityDefinition);

        // Expect setValue to be called with data['update_value']
        $testEntity->expects($this->once())
            ->method('setValue')
            ->with(
                $this->equalTo('name'),
                $this->equalTo('edited test')
            );

        // Stub the user to satisfy requirements for call to execute
        $user = $this->createStub(UserEntity::class);

        // Make sure we save the entity we passed in
        $this->mockEntityLoader->expects($this->once())
            ->method('save')
            ->with(
                $this->equalTo($testEntity),
                $this->equalTo($user)
            );

        // Execute
        $this->assertTrue($this->executor->execute($testEntity, $user));
    }

    /**
     * Make sure we can update a multi-value field
     */
    public function testExecuteWithMultiValue(): void
    {
        $testData = [
            'update_field' => 'members',
            'update_value' => 'FAKE-UUID'
        ];

        // Set the entity action data
        $this->mockActionEntity->method("getData")->willReturn($testData);

        // Create a mock test entity, with a mock definition that gets a field
        // This is important because the execute function will make sure the field
        // exists and get the type of data from the field definition
        $testEntity = $this->createMock(EntityInterface::class);
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $field = new Field();
        $field->type = Field::TYPE_OBJECT_MULTI;
        $mockEntityDefinition->method('getField')->willReturn($field);
        $testEntity->method("getDefinition")->willReturn($mockEntityDefinition);

        // Expect setValue to be called with data['update_value']
        $testEntity->expects($this->once())
            ->method('addMultiValue')
            ->with(
                $this->equalTo($testData['update_field']),
                $this->equalTo($testData['update_value'])
            );

        // Stub the user to satisfy requirements for call to execute
        $user = $this->createStub(UserEntity::class);

        // Execute
        $this->assertTrue($this->executor->execute($testEntity, $user));
    }

    /**
     * Make sure it fails with no data
     */
    public function testExecuteFailOnBadParams(): void
    {
        $testEntity = $this->createMock(EntityInterface::class);
        $user = $this->createStub(UserEntity::class);

        $this->assertFalse($this->executor->execute($testEntity, $user));
        $this->assertNotNull($this->executor->getLastError());
    }

    /**
     * Assure that it fails if we try to update a field that does not exist
     */
    public function testExecuteFailOnBadField(): void
    {
        // Set the entity action data
        $this->mockActionEntity->method("getData")->willReturn([
            'update_field' => 'name',
            'update_value' => 'edited test'
        ]);

        // Create mock definition, but no mock field (returns null)
        $testEntity = $this->createMock(EntityInterface::class);
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $mockEntityDefinition->method('getField')->willReturn(null);
        $testEntity->method("getDefinition")->willReturn($mockEntityDefinition);


        // Stub the user to satisfy requirements for call to execute
        $user = $this->createStub(UserEntity::class);

        $this->assertFalse($this->executor->execute($testEntity, $user));
        $this->assertNotNull($this->executor->getLastError());
    }
}
