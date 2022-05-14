<?php

declare(strict_types=1);

namespace NetricTest\Handler;

use Netric\Account\AccountContainer;
use PHPUnit\Framework\TestCase;
use Netric\Entity\Entity;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Handler\EntityHandler;
use Netric\Permissions\Dacl;
use Netric\Permissions\DaclLoader;
use Ramsey\Uuid\Uuid;

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

    /**
     * Fake loading DACLs
     *
     * @var DaclLoader
     */
    private DaclLoader $mockDaclLoader;

    protected function setUp(): void
    {
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockDaclLoader = $this->createMock(DaclLoader::class);

        // Create the handler with mocks
        $this->entityHandler = new EntityHandler(
            $this->mockEntityLoader,
            $this->mockDaclLoader
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
        $task = new Entity(
            $taskDefinition,
            $this->createMock(EntityLoader::class),
            $this->createMock(GroupingLoader::class)
        );

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

    /**
     * Test getting entity data and checking permissions
     *
     * @return void
     */
    public function testGetEntityDataById(): void
    {
        // Create some UUIDs for the test
        $taskEntityId = Uuid::uuid4()->toString();
        $accountId = Uuid::uuid4()->toString();
        $userId = Uuid::uuid4()->toString();

        $daclPermissions = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];
        $daclDetails = [
            'entries' => [],
            'name' => 'task_dacl'
        ];

        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);
        $mockTaskEntity->method('getName')->willReturn('Test Task');
        $mockTaskEntity->method('getEntityId')->willReturn($taskEntityId);
        $mockTaskEntity->method('toArrayWithApplied')->willReturn([
            'obj_type' => 'task',
            'entity_id' => $taskEntityId,
            'name' => 'Test Task',
            'description' => 'Task for testing',
            'applied_name' => 'Test Task',
            'applied_icon' => '',
            'applied_description' => ''
        ]);

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
            [$taskEntityId, $accountId, $mockTaskEntity],
            [$userId, $accountId, $user],
        ]));

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn($daclDetails);

        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure getGetAction is called and we get a response
        $response = $this->entityHandler->getEntityDataById($taskEntityId, $userId, $accountId);
        $this->assertEquals([
            'obj_type' => 'task',
            'entity_id' => $taskEntityId,
            'name' => 'Test Task',
            'description' => 'Task for testing',
            'applied_name' => 'Test Task',
            'applied_icon' => '',
            'applied_description' => '',
            'applied_dacl' => [
                'entries' => [],
                'name' => 'task_dacl'
            ],
            'applied_user_permissions' => $daclPermissions
        ], json_decode($response, true));
    }
}
