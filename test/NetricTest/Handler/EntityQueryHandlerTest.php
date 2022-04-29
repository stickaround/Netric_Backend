<?php

declare(strict_types=1);

namespace NetricTest\Handler;

use Netric\Account\Account;
use Netric\Account\AccountContainer;
use Netric\Account\AccountContainerInterface;
use PHPUnit\Framework\TestCase;
use Netric\Entity\Entity;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\Results;
use Netric\Handler\EntityQueryHandler;
use Netric\Permissions\Dacl;
use Netric\Permissions\DaclLoader;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class EntityQueryHandlerTest extends TestCase
{
    /**
     * Initialized Handler with mock dependencies
     */
    private EntityQueryHandler $entityHandler;

    /**
     * Dependency mocks
     */
    private EntityLoader $mockEntityLoader;

    /**
     * Mock account
     *
     * @var Account
     */
    private Account $mockAccount;

    /**
     * Mock entity index
     *
     * @var IndexInterface
     */
    private IndexInterface $mockIndex;

    /**
     * Mock dacl loader to play with permission scenarios
     *
     * @var DaclLoader
     */
    private DaclLoader $mockDaclLoader;

    protected function setUp(): void
    {
        // Create mocks
        $this->mockIndex = $this->createMock(IndexInterface::class);
        $this->mockDaclLoader = $this->createMock(DaclLoader::class);
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->mockAccount->method('getAccountId')->willReturn(Uuid::uuid4()->toString());
        $this->mockAccount->method('getName')->willReturn('netrictest');
        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $accountContainer->method('loadById')->willReturn($this->mockAccount);

        // Create the handler with mocks
        $this->entityHandler = new EntityQueryHandler(
            $accountContainer,
            $this->mockDaclLoader,
            $this->mockIndex,
            $this->mockEntityLoader
        );
    }

    /**
     * Test the executing of entity query
     */
    public function testExecute()
    {
        $taskEntityId = Uuid::uuid4()->toString();
        $daclPermissions = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];
        $daclDetails = [
            'entries' => [],
            'name' => 'task_dacl'
        ];
        $taskEntityData = [
            'obj_type' => 'task',
            'entity_id' => $taskEntityId,
            'name' => 'Test Task',
            'description' => 'Task for testing'
        ];

        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);
        $mockTaskEntity->method('getName')->willReturn('Test Task');
        $mockTaskEntity->method('getEntityId')->willReturn($taskEntityId);
        $mockTaskEntity->method('toArrayWithApplied')->willReturn(
            [
                'obj_type' => 'task',
                'entity_id' => $taskEntityId,
                'name' => 'Test Task',
                'description' => 'Task for testing',
                'applied_name' => $taskEntityData['name'],
                'applied_icon' => '',
                'applied_description' => '',
            ]
        );

        // Create a test user
        $mockUser = $this->createMock(UserEntity::class);
        $mockUser->method("getEntityId")->willReturn(UUid::uuid4()->toString());
        $this->mockEntityLoader->method('getEntityById')
            ->with($mockUser->getEntityId(), $this->mockAccount->getAccountId())
            ->will($this->returnValue($mockUser));

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn($daclDetails);

        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        $result = $this->createMock(Results::class);
        $result->method('getTotalNum')->willReturn(1);
        $result->method('getNum')->willReturn(1);
        $result->method('getEntity')->willReturn($mockTaskEntity);
        $this->mockIndex->method('executeQuery')->willReturn($result);

        // Make sure postExecuteAction is called and we get a response
        $response = $this->entityHandler->execute(
            $mockUser->getEntityId(),
            $this->mockAccount->getAccountId(),
            json_encode(['obj_type' => 'task'])
        );
        $responseData = json_decode($response, true);
        $this->assertTrue(isset($responseData['query_ran']));
        $this->assertTrue(isset($responseData['total_num']));
        $this->assertTrue(isset($responseData['offset']));
        $this->assertTrue(isset($responseData['limit']));
        $this->assertEquals(1, count($responseData['entities']));
    }
}
