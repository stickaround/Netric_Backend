<?php

/**
 * Test the permission controller
 */

namespace NetricTest\Controller;

use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Controller\PermissionController;
use Netric\Entity\EntityLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityGroupings\EntityGroupings;
use Netric\Permissions\Dacl\Entry;
use Netric\Permissions\Dacl;
use Netric\Permissions\DaclLoader;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class PermissionControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private PermissionController $permissionController;

    /**
     * Dependency mocks
     */
    private EntityLoader $mockEntityLoader;
    private AuthenticationService $mockAuthService;
    private EntityDefinitionLoader $mockEntityDefinitionLoader;
    private GroupingLoader $mockGroupingLoader;
    private DaclLoader $mockDaclLoader;
    private Account $mockAccount;

    protected function setUp(): void
    {
        // Create mocks
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockEntityDefinitionLoader = $this->createMock(EntityDefinitionLoader::class);
        $this->mockGroupingLoader = $this->createMock(GroupingLoader::class);        
        $this->mockDaclLoader = $this->createMock(DaclLoader::class);

        // Provide identity for mock auth service
        $this->mockAuthService = $this->createMock(AuthenticationService::class);
        $ident = new AuthenticationIdentity('blahaccount', 'blahuser');
        $this->mockAuthService->method('getIdentity')->willReturn($ident);

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->mockAccount->method('getAccountId')->willReturn(Uuid::uuid4()->toString());

        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $accountContainer->method('loadById')->willReturn($this->mockAccount);

        // Create the controller with mocks
        $this->permissionController = new PermissionController(
            $accountContainer,
            $this->mockAuthService,
            $this->mockEntityLoader,
            $this->mockEntityDefinitionLoader,
            $this->mockGroupingLoader,
            $this->mockDaclLoader
        );
        $this->permissionController->testMode = true;
    }

    /**
     * Test the gettinf of dacl entries
     */
    public function testGetGetDaclForEntityAction()
    {
        $userEntityId = Uuid::uuid4()->toString();
        $userEntityDetails = [
            'obj_type' => ObjectTypes::USER,
            'entity_id' => $userEntityId,
            'name' => 'Test Task',
            'description' => 'Task for testing'
        ];

        $taskDefId = Uuid::uuid4()->toString();
        $taskDefDetails = [
            'obj_type' => ObjectTypes::TASK,
            'entity_definition_id' => $taskDefId,
            'name' => 'Task Def',
            'description' => 'Task Entity Object Definition'
        ];

        $groupId = Uuid::uuid4()->toString();
        $groupDetails = [
            "group_id" => $groupId,
            "name" => 'Test Group',
            "f_system" => true,
            "sort_order" => 1,
            "commit_id" => 1
        ]; 

        $daclPermissions = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];

        $daclDetails = [
            'entries' => [],
            'name' => 'task_dacl'
        ];        

        // Create test user entity
        $mockUserEntity = $this->createMock(UserEntity::class);
        $mockUserEntity->method('getName')->willReturn('Test Task');
        $mockUserEntity->method('getEntityId')->willReturn($userEntityId);
        $mockUserEntity->method('toArray')->willReturn($userEntityDetails);
        
        // Mock the entity loader service which is used to load the user by guid
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockUserEntity);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('getUsers')->willReturn([$userEntityId]);
        $mockDacl->method('toArray')->willReturn($daclDetails);        
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntityDefinition')->willReturn($mockDacl);

        // Create task definition for testing
        $mockTaskDef = $this->createMock(EntityDefinition::class);
        $mockTaskDef->method('getObjType')->willReturn(ObjectTypes::TASK);
        $mockTaskDef->method('toArray')->willReturn($taskDefDetails);

        // Mock the entity definition loader service which is used to load entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockTaskDef);

        // Create the group for testing
        $mockEntityGroup = $this->createMock(Group::class);
        $mockEntityGroup->method('toArray')->willReturn($groupDetails);

        // Create the entity groupings for testing
        $mockEntityGroupings = $this->createMock(EntityGroupings::class);        
        $mockEntityGroupings->method('toArray')->willReturn([$groupDetails]);

        // Mock the grouping loader service which is used to get the entity groupings
        $this->mockGroupingLoader->method('get')->willReturn($mockEntityGroupings);

        // Make sure getGetAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setParam('obj_type', ObjectTypes::TASK);        
        $response = $this->permissionController->getGetDaclForEntityAction($request);
        $this->assertEquals(array_merge($daclDetails, [
            'user_names' => [
                $userEntityId => 'Test Task'
            ],
            'group_names' => [
                $groupId => 'Test Group'
            ]
        ]), $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in getting dacl entries
     */
    public function testGetGetDaclForEntityActionCatchingErrors()
    {
        // Make sure getGetDaclForEntityAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setParam('bogus', 'data');        
        $response = $this->permissionController->getGetDaclForEntityAction($request);

        // It should return an error if no obj_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());
    }

    /**
     * Test the saving of DACL Entries
     */
    public function testPostSaveDaclEntriesAction()
    {
        $taskEntityId = Uuid::uuid4()->toString();
        $taskEntityDetails = [
            'obj_type' => ObjectTypes::TASK,
            'entity_id' => $userEntityId,
            'name' => 'Test Task',
            'description' => 'Task for testing'
        ];

        $taskDefId = Uuid::uuid4()->toString();
        $taskDefDetails = [
            'obj_type' => ObjectTypes::TASK,
            'entity_definition_id' => $taskDefId,
            'name' => 'Task Def',
            'description' => 'Task Entity Object Definition'
        ];

        $groupId = Uuid::uuid4()->toString();
        $groupDetails = [
            "group_id" => $groupId,
            "name" => 'Test Group',
            "f_system" => true,
            "sort_order" => 1,
            "commit_id" => 1
        ]; 

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
        $mockTaskEntity = $this->createMock(UserEntity::class);
        $mockTaskEntity->method('getName')->willReturn('Test Task');
        $mockTaskEntity->method('getEntityId')->willReturn($taskEntityId);
        $mockTaskEntity->method('toArray')->willReturn($taskEntityDetails);
        
        // Mock the entity loader service which is used to load the task by guid
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity);
        $this->mockEntityLoader->method('save')->willReturn($taskEntityId);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('getUsers')->willReturn([$userEntityId]);
        $mockDacl->method('toArray')->willReturn($daclDetails);        
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntityDefinition')->willReturn($mockDacl);

        // Create task definition for testing
        $mockTaskDef = $this->createMock(EntityDefinition::class);
        $mockTaskDef->method('getObjType')->willReturn(ObjectTypes::TASK);
        $mockTaskDef->method('toArray')->willReturn($taskDefDetails);

        // Mock the entity definition loader service which is used to load entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockTaskDef);

        // Create the group for testing
        $mockEntityGroup = $this->createMock(Group::class);
        $mockEntityGroup->method('toArray')->willReturn($groupDetails);

        // Create the entity groupings for testing
        $mockEntityGroupings = $this->createMock(EntityGroupings::class);        
        $mockEntityGroupings->method('toArray')->willReturn([$groupDetails]);

        // Mock the grouping loader service which is used to get the entity groupings
        $this->mockGroupingLoader->method('get')->willReturn($mockEntityGroupings);

        // Make sure postSaveDaclEntriesAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => ObjectTypes::TASK]));
        $response = $this->permissionController->postSaveDaclEntriesAction($request);
        $this->assertEquals([
            'entries' => [],
            'name' => 'task_dacl'
        ], $response->getOutputBuffer());

        // Now let's test with entity_id provided in the params
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => ObjectTypes::TASK, 'entity_id' => $mockTaskEntity]));
        $response = $this->permissionController->postSaveDaclEntriesAction($request);
        $this->assertEquals([
            'entries' => [],
            'name' => 'task_dacl'
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in saving DACL entries
     */
    public function testPostSaveDaclEntriesActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->permissionController->postSaveDaclEntriesAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postSaveDaclEntriesAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->permissionController->postSaveDaclEntriesAction($request);

        // It should return an error if no obj_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());
    }
}
