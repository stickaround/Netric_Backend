<?php

declare(strict_types=1);

namespace NetricTest\Handler;

use Netric\Account\AccountContainer;
use PHPUnit\Framework\TestCase;
use Netric\Entity\Entity;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Handler\EntityHandler;

/**
 * @group integration
 */
class EntityHandlerTest extends TestCase
{
    /**
     * Initialized Handler with mock dependencies
     */
    private EntityHandler $entityHandler;

    /**
     * Dependency mocks
     */
    private EntityLoader $mockEntityLoader;

    protected function setUp(): void
    {
        // Provide identity for mock auth service
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);

        // Create the handler with mocks
        $this->entityHandler = new EntityHandler(
            $this->mockEntityLoader,
        );
    }

    /**
     * Test the checking in of the user
     */
    public function testSetEntitySeenBy()
    {
        // Create some test UUIDs
        $entityId = 'fe682cf2-a31b-4d0e-93d0-f87c7aa01dd4';
        $userId = '9e90f619-94f6-4f4b-82c0-3aeba561222c';
        $accountId = '9aaff4c1-ec2b-4513-b82f-7b4ce9c2241c';

        // Setup a task entity
        $taskDefinition = new EntityDefinition('task', $accountId);
        $field = new Field('seen_by');
        $field->type = Field::TYPE_OBJECT_MULTI;
        $taskDefinition->addField($field);
        $task = new Entity($taskDefinition);

        // Setup user
        $userDefinition = new EntityDefinition('user', $accountId);
        $nameField = new Field('name');
        $nameField->type = Field::TYPE_TEXT;
        $userDefinition->addField($nameField);
        $user = new UserEntity(
            $userDefinition,
            $this->mockEntityLoader,
            $this->createMock(GroupingLoader::class),
            $this->createMock(AccountContainer::class)
        );
        $user->setValue('name', 'test-user');

        // Mock getEntityById for task and user, the last param in the map is the return value
        $this->mockEntityLoader->method('getEntityById')->will($this->returnValueMap([
            [$entityId, $accountId, $task],
            [$userId, $accountId, $user],
        ]));

        // Mock save
        $this->mockEntityLoader->expects($this->once())->method('save')->willReturn($entityId);

        // Call the handler
        $this->entityHandler->setEntitySeenBy($entityId, $userId, $accountId);

        // Make sure the 'seen_by' field was updated in the task entity
        $this->assertEquals([$userId], $task->getValue('seen_by'));
    }
}
