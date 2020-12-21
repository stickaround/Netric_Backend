<?php

/**
 * Test the entity query controller
 */

namespace NetricTest\Controller;

use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Permissions\DaclLoader;
use Netric\Permissions\Dacl;
use Netric\Controller\EntityQueryController;
use Netric\Entity\ObjType\TaskEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Results;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class EntityQueryControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private EntityQueryController $entityQueryController;

    /**
     * Dependency mocks
     */    
    private AuthenticationService $mockAuthService;
    private EntityDefinitionLoader $mockEntityDefinitionLoader;    
    private RelationalDbContainer $mockDbContainer;
    private DaclLoader $mockDaclLoader;
    private Account $mockAccount;

    /**
     * Test entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    protected function setUp(): void
    {
        // Create mocks                
        $this->index = $this->createMock(IndexInterface::class);
        $this->mockDaclLoader = $this->createMock(DaclLoader::class);

        // Provide identity for mock auth service
        $this->mockAuthService = $this->createMock(AuthenticationService::class);
        $ident = new AuthenticationIdentity('blahaccount', 'blahuser');
        $this->mockAuthService->method('getIdentity')->willReturn($ident);

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->mockAccount->method('getAccountId')->willReturn(Uuid::uuid4()->toString());
        $this->mockAccount->method('getName')->willReturn('netrictest');
        
        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $accountContainer->method('loadById')->willReturn($this->mockAccount);        

        // Create the controller with mocks
        $this->entityQueryController = new EntityQueryController(
            $accountContainer,
            $this->mockAuthService,            
            $this->mockDaclLoader,
            $this->index
        );

        $this->entityQueryController->testMode = true;
    }

    /**
     * Test the executing of entity query
     */
    public function testPostExecuteAction()
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
        $mockTaskEntity->method('toArray')->willReturn($taskEntityData);
        
        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn($daclDetails);

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

        $this->index->method('executeQuery')->willReturn($result);

        // Make sure postExecuteAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task']));
        $response = $this->entityQueryController->postExecuteAction($request);
        $this->assertEquals([
            'total_num' => 1,
            'offset' => 0,
            'limit' => 100,
            'num' => 1,
            'query_ran' => [
                'obj_type' => 'task',
                'limit' => 100,
                'offset' => 0,
                'conditions' => [],
                'order_by' => []
            ],
            'account' => 'netrictest',
            'entities' => [
                array_merge($taskEntityData, [
                    'applied_dacl' => $daclDetails,
                    'currentuser_permissions' => $daclPermissions
                ])
            ]
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in executing an entity query
     */
    public function testPostExecuteActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityQueryController->postExecuteAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postExecuteAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityQueryController->postExecuteAction($request);

        // It should return an error if no obj_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());
    }
}
