<?php

namespace NetricTest\Controller;

use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Controller\EntityController;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\Forms;
use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\BrowserView\BrowserViewService;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityQuery\Where;
use Netric\Permissions\DaclLoader;
use Netric\Permissions\Dacl;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class EntityControllerTest extends TestCase
{
    /**
     * Account used for testing
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Controller instance used for testing
     *
     * @var EntityController
     */
    protected $controller = null;

    /**
     * Group ids to cleanup
     *
     * @var array
     */
    private $testGroups = [];

    /**
     * Test entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Test entity definitions that should be cleaned up on tearDown
     *
     * @var DefinitionInterface[]
     */
    private $testDefinitions = [];

    /**
     * Initialized controller with mock dependencies
     */
    private EntityController $entityController;

    /**
     * Dependency mocks
     */
    private EntityLoader $mockEntityLoader;
    private AuthenticationService $mockAuthService;
    private EntityDefinitionLoader $mockEntityDefinitionLoader;
    private GroupingLoader $mockGroupingLoader;
    private BrowserViewService $mockBrowserViewService;
    private Forms $mockForms;
    private RelationalDbContainer $mockDbContainer;
    private DaclLoader $mockDaclLoader;
    private Account $mockAccount;

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Create mocks
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockEntityDefinitionLoader = $this->createMock(EntityDefinitionLoader::class);
        $this->mockGroupingLoader = $this->createMock(GroupingLoader::class);
        $this->mockBrowserViewService = $this->createMock(BrowserViewService::class);
        $this->mockForms = $this->createMock(Forms::class);
        $this->mockDatabaseContainer = $this->createMock(RelationalDbContainer::class);
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
        $this->entityController = new EntityController(
            $accountContainer,
            $this->mockAuthService,
            $this->mockEntityLoader,
            $this->mockEntityDefinitionLoader,
            $this->mockGroupingLoader,
            $this->mockBrowserViewService,
            $this->mockForms,            
            $this->mockDaclLoader,
            $this->mockDatabaseContainer
        );
        $this->entityController->testMode = true;
    }

    /**
     * Test the getting of entity
     */
    public function testGetEntityAction()
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

        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);
        $mockTaskEntity->method('getName')->willReturn('Test Task');
        $mockTaskEntity->method('getEntityId')->willReturn($taskEntityId);
        $mockTaskEntity->method('toArray')->willReturn([
            'obj_type' => 'task',
            'entity_id' => $taskEntityId,
            'name' => 'Test Task',
            'description' => 'Task for testing'
        ]);
        
        // Mock the entity loader service which is used to load the task by guid
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn($daclDetails);
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure getGetAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['entity_id' => $taskEntityId]));
        $response = $this->entityController->getGetAction($request);
        $this->assertEquals([
            'obj_type' => 'task',
            'entity_id' => $taskEntityId,
            'name' => 'Test Task',
            'description' => 'Task for testing',
            'applied_dacl' => [
                'entries' => [],
                'name' => 'task_dacl'
            ],
            'currentuser_permissions' => $daclPermissions
        ], $response->getOutputBuffer());
    }

    /**
     * Try getting an entity that we have no permission
     */
    public function testGetEntityActionNoPermission()
    {
        $taskEntityId = Uuid::uuid4()->toString();

        // Set the view to false, since we can to test if the view permission is not allowed
        $daclPermissions = [
            'view' => false
        ];

        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);
        $mockTaskEntity->method('getName')->willReturn('Test Task');
        $mockTaskEntity->method('getEntityId')->willReturn($taskEntityId);
        $mockTaskEntity->method('toArray')->willReturn([
            'obj_type' => 'task',
            'entity_id' => $taskEntityId,
            'name' => 'Test Task',
            'description' => 'Task for testing'
        ]);
        
        // Mock the entity loader service which is used to load the task by guid
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure getGetAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['entity_id' => $taskEntityId]));
        $response = $this->entityController->getGetAction($request);

        // It should only return the entity_id, name, and current permission
        $this->assertEquals([
            'entity_id' => $taskEntityId,
            'name' => 'Test Task',            
            'currentuser_permissions' => $daclPermissions
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the errors being thrown in getting an entity
     */
    public function testGetEntityActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->getGetAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure getGetAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->getGetAction($request);

        // It should return an error if no entity_id is provided in the params
        $this->assertEquals(['error' => 'entity_id or uname are required params.'], $response->getOutputBuffer());
    }

    /**
     * Test the getting of entity definition
     */
    public function testDefinitionAction()
    {
        $definitionId = Uuid::uuid4()->toString();
        $taskObjDetails = [
            'obj_type' => 'task',
            'entity_definition_id' => $definitionId,
            'name' => 'Task',
            'description' => 'Task Entity Object Definition'
        ];

        $daclDetails = [
            'entries' => [],
            'name' => 'task_dacl'
        ];

        $formDetails = [
            'small' => 'Small Forms',
            'medium' => 'Medium Forms',
            'large' => 'Large Forms'
        ];

        $browserViewDetails = [
            'my_tasks' => [
                'obj_type' => 'task',
                'name' => 'My Incomplete Tasks',
                'description' => 'Incomplete tasks assigned to me',
                'default' => true,
                'conditions' => [
                    'user' => [
                        'blogic' => Where::COMBINED_BY_AND,
                        'field_name' => 'owner_id',
                        'operator' => Where::OPERATOR_EQUAL_TO,
                        'value' => UserEntity::USER_CURRENT,
                    ]
                ]
            ]
        ];

        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('getObjType')->willReturn('task');
        $mockDefinition->method('toArray')->willReturn($taskObjDetails);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('toArray')->willReturn($daclDetails);
                
        // Mock the entity definition loader service which is used to load entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        // Mock the forms service which is used to get the entity definition forms
        $this->mockForms->method('getDeviceForms')->willReturn($formDetails);
        
        // Mock the browser view service which is used to get the browser views for the user
        $this->mockBrowserViewService->method('getViewsForUser')->willReturn([]);
        $this->mockBrowserViewService->method('getDefaultViewForUser')->willReturn($browserViewDetails);

        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntityDefinition')->willReturn($mockDacl);
        
        // Make sure getGetDefinitionAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task']));
        $response = $this->entityController->getGetDefinitionAction($request);

        // It should only return the entity_id, name, and current permission
        $this->assertEquals([
            'obj_type' => 'task',
            'entity_definition_id' => $definitionId,
            'name' => 'Task',
            'description' => 'Task Entity Object Definition',
            'browser_mode' => 'table',
            'forms' => $formDetails,
            'views' => [],
            'default_view' => $browserViewDetails,
            'applied_dacl' => $daclDetails
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the errors being thrown when getting an entity definition
     */
    public function testDefinitionActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->getGetDefinitionAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure getGetDefinitionAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->getGetDefinitionAction($request);

        // It should return an error if no obj_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());

        // Mock the entity definition loader service that it will return a null value for entity definition        
        $this->mockEntityDefinitionLoader->method('get')->willReturn(null);

        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task']));
        $response = $this->entityController->getGetDefinitionAction($request);

        // It should return an error if no entity definition is found
        $this->assertEquals(['error' => 'task could not be loaded.'], $response->getOutputBuffer());
    }

    /**
     * Test the getting of all the entity definitions
     */
    public function testAllDefinitionsAction()
    {
        $taskDefId = Uuid::uuid4()->toString();        
        $taskDefDetails = [
            'obj_type' => 'task',
            'entity_definition_id' => $taskDefId,
            'name' => 'Task Def',
            'description' => 'Task Entity Object Definition'
        ];

        $noteDefId = Uuid::uuid4()->toString();
        $noteDefDetails = [
            'obj_type' => 'note',
            'entity_definition_id' => $noteDefId,
            'name' => 'Note Def',
            'description' => 'Note Entity Object Definition'
        ];

        $daclDetails = [
            'entries' => [],
            'name' => 'definition_dacl'
        ];

        $formDetails = [
            'small' => 'Small Forms',
            'medium' => 'Medium Forms',
            'large' => 'Large Forms'
        ];

        $browserViewDetails = [
            'my_tasks' => [
                'obj_type' => 'task',
                'name' => 'My Incomplete Tasks',
                'description' => 'Incomplete tasks assigned to me',
                'default' => true,
                'conditions' => [
                    'user' => [
                        'blogic' => Where::COMBINED_BY_AND,
                        'field_name' => 'owner_id',
                        'operator' => Where::OPERATOR_EQUAL_TO,
                        'value' => UserEntity::USER_CURRENT,
                    ]
                ]
            ]
        ];

        // Create task definition for testing
        $mockTaskDef = $this->createMock(EntityDefinition::class);
        $mockTaskDef->method('getObjType')->willReturn('task');
        $mockTaskDef->method('toArray')->willReturn($taskDefDetails);

        // Create note definition for testing
        $mockNoteDef = $this->createMock(EntityDefinition::class);
        $mockNoteDef->method('getObjType')->willReturn('note');
        $mockNoteDef->method('toArray')->willReturn($noteDefDetails);

        // Create test dacl permission for the entity definitions
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('toArray')->willReturn($daclDetails);
                
        // Mock the entity definition loader service which is used to load entity definition
        $this->mockEntityDefinitionLoader->method('getAll')->willReturn([$mockTaskDef, $mockNoteDef]);

        // Mock the forms service which is used to get the entity definition forms
        $this->mockForms->method('getDeviceForms')->willReturn($formDetails);
        
        // Mock the browser view service which is used to get the browser views for the user
        $this->mockBrowserViewService->method('getViewsForUser')->willReturn([]);
        $this->mockBrowserViewService->method('getDefaultViewForUser')->willReturn($browserViewDetails);

        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntityDefinition')->willReturn($mockDacl);
        
        // Make sure getAllDefinitionsAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);        
        $response = $this->entityController->getAllDefinitionsAction($request);

        // It should only return the entity_id, name, and current permission
        $definitionDetails = [
            'browser_mode' => 'table',
            'forms' => $formDetails,
            'views' => [],
            'default_view' => $browserViewDetails,
            'applied_dacl' => $daclDetails
        ];
        $this->assertEquals([
            array_merge($taskDefDetails, $definitionDetails),
            array_merge($noteDefDetails, $definitionDetails)
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the errors being thrown when getting all the entity definitions
     */
    public function testAllDefinitionsActionCatchingErrors()
    {
        // Mock the entity definition loader service that it will return an empty array when getting all the entity definitions
        $this->mockEntityDefinitionLoader->method('get')->willReturn([]);

        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);        
        $response = $this->entityController->getAllDefinitionsAction($request);

        // It should return an error
        $this->assertEquals(['error' => 'Definitions could not be loaded.'], $response->getOutputBuffer());
    }

    /**
     * Test the updating of entity definition
     */
    public function testUpdateEntityDefAction()
    {
        $definitionId = Uuid::uuid4()->toString();
        $taskObjDetails = [
            'obj_type' => 'task',
            'entity_definition_id' => $definitionId,
            'name' => 'Task',
            'description' => 'Task Entity Object Definition'
        ];

        $daclDetails = [
            'entries' => [],
            'name' => 'task_dacl'
        ];

        $formDetails = [
            'small' => 'Small Forms',
            'medium' => 'Medium Forms',
            'large' => 'Large Forms'
        ];

        $browserViewDetails = [
            'my_tasks' => [
                'obj_type' => 'task',
                'name' => 'My Incomplete Tasks',
                'description' => 'Incomplete tasks assigned to me',
                'default' => true,
                'conditions' => [
                    'user' => [
                        'blogic' => Where::COMBINED_BY_AND,
                        'field_name' => 'owner_id',
                        'operator' => Where::OPERATOR_EQUAL_TO,
                        'value' => UserEntity::USER_CURRENT,
                    ]
                ]
            ]
        ];

        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('getObjType')->willReturn('task');
        $mockDefinition->method('toArray')->willReturn($taskObjDetails);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('toArray')->willReturn($daclDetails);
                
        // Mock the entity definition loader service which is used to load entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        // Mock the forms service which is used to get the entity definition forms
        $this->mockForms->method('getDeviceForms')->willReturn($formDetails);
        
        // Mock the browser view service which is used to get the browser views for the user
        $this->mockBrowserViewService->method('getViewsForUser')->willReturn([]);
        $this->mockBrowserViewService->method('getDefaultViewForUser')->willReturn($browserViewDetails);

        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntityDefinition')->willReturn($mockDacl);
        
        // Make sure postUpdateEntityDefAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'entity_definition_id' => $definitionId]));
        $response = $this->entityController->postUpdateEntityDefAction($request);

        // It should only return the entity_id, name, and current permission
        $this->assertEquals([
            'obj_type' => 'task',
            'entity_definition_id' => $definitionId,
            'name' => 'Task',
            'description' => 'Task Entity Object Definition',
            'browser_mode' => 'table',
            'forms' => $formDetails,
            'views' => [],
            'default_view' => $browserViewDetails,
            'applied_dacl' => $daclDetails
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the errors being thrown when updating an entity definition
     */
    public function testUpdateEntityDefActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->getGetAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postUpdateEntityDefAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->postUpdateEntityDefAction($request);

        // It should return an error if no object_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());

        // Make sure postUpdateEntityDefAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => '']));
        $response = $this->entityController->postUpdateEntityDefAction($request);

        // It should return an error if object type provided is empty
        $this->assertEquals(['error' => 'obj_type should not be empty.'], $response->getOutputBuffer());

        // Mock the entity definition loader service to load an empty entity def so we can catch the error
        $this->mockEntityDefinitionLoader->method('get')->willReturn(null);

        // Make sure postUpdateEntityDefAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'entity_definition_id' => Uuid::uuid4()->toString()]));
        $response = $this->entityController->postUpdateEntityDefAction($request);

        // It should return an error if object type provided is empty
        $this->assertEquals(['error' => 'Definition not found.'], $response->getOutputBuffer());
    }

    /**
     * Test the deleting of entity definition
     */
    public function testDeleteEntityDefAction()
    {
        $definitionId = Uuid::uuid4()->toString();
        $taskObjDetails = [
            'obj_type' => 'task',
            'entity_definition_id' => $definitionId,
            'name' => 'Task',
            'description' => 'Task Entity Object Definition'
        ];

        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('getSystem')->willReturn(false);
        $mockDefinition->method('getEntityDefinitionId')->willReturn(Uuid::uuid4()->toString());
        $mockDefinition->method('getAccountId')->willReturn($this->mockAccount->getAccountId());
                        
        // Mock the entity definition loader service which is used to load entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);
        $this->mockEntityDefinitionLoader->method('delete')->willReturn(true);        
                
        // Make sure postDeleteEntityDefAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task']));
        $response = $this->entityController->postDeleteEntityDefAction($request);

        // It should return true when successfully deleted an entity definition
        $this->assertTrue($response->getOutputBuffer());
    }

    /**
     * Catch the errors being thrown when deleting an entity definition
     */
    public function testDeleteEntityDefActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->getGetAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postDeleteEntityDefAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->postDeleteEntityDefAction($request);

        // It should return an error if no object_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());

        // Mock the entity definition loader service to load an empty entity def so we can catch the error
        $this->mockEntityDefinitionLoader->method('get')->willReturn(null);

        // Make sure postUpdateEntityDefAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task']));
        $response = $this->entityController->postDeleteEntityDefAction($request);

        // It should return an error if object type provided is empty
        $this->assertEquals(['error' => 'task could not be loaded.'], $response->getOutputBuffer());
    }

    /**
     * Test the getting of entity definition
     */
    public function testPostSaveAction()
    {
        $savedTaskEntityId = Uuid::uuid4()->toString();
        $daclPermissions = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];
        $taskObjDetails = [
            'obj_type' => 'task',
            'entity_definition_id' => $definitionId,
            'name' => 'Task',
            'description' => 'Task Entity Object Definition'
        ];
        $daclDetails = [
            'entries' => [],
            'name' => 'task_dacl'
        ];

        // Create test name field
        $mockNameField = $this->createMock(Field::class);        

        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('getObjType')->willReturn('task');
        $mockDefinition->method('getFields')->willReturn([$mockNameField]);
        $mockDefinition->method('toArray')->willReturn($taskObjDetails);

        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);
        $mockTaskEntity->method('getName')->willReturn('Test Task');        
        $mockTaskEntity->method('getDefinition')->willReturn($mockDefinition);
        $mockTaskEntity->method('toArray')->willReturn([
            'obj_type' => 'task',
            'entity_id' => $savedTaskEntityId,
            'name' => 'Test Task',
            'description' => 'Task for saving'
        ]);
        
        // Mock the entity loader service which is used to create a new entity and can save it
        $this->mockEntityLoader->method('create')->willReturn($mockTaskEntity);
        $this->mockEntityLoader->method('save')->willReturn($savedTaskEntityId);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn($daclDetails);
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task']));
        $response = $this->entityController->postSaveAction($request);
        $this->assertEquals([
            'obj_type' => 'task',
            'entity_id' => $savedTaskEntityId,
            'name' => 'Test Task',
            'description' => 'Task for saving',
            'applied_dacl' => $daclDetails,
            'currentuser_permissions' => $daclPermissions
        ], $response->getOutputBuffer());
    }

    /**
     * Test the saving of existing entity
     */
    public function testPostSaveActionExistingEntity()
    {
        $existingEntityId = Uuid::uuid4()->toString();
        $daclPermissions = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];
        $taskObjDetails = [
            'obj_type' => 'task',
            'entity_definition_id' => $definitionId,
            'name' => 'Task',
            'description' => 'Task Entity Object Definition'
        ];
        $daclDetails = [
            'entries' => [],
            'name' => 'task_dacl'
        ];

        // Create test name field
        $mockNameField = $this->createMock(Field::class);        

        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('getObjType')->willReturn('task');
        $mockDefinition->method('getFields')->willReturn([$mockNameField]);
        $mockDefinition->method('toArray')->willReturn($taskObjDetails);

        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);
        $mockTaskEntity->method('getName')->willReturn('Test Task');        
        $mockTaskEntity->method('getDefinition')->willReturn($mockDefinition);
        $mockTaskEntity->method('toArray')->willReturn([
            'obj_type' => 'task',
            'entity_id' => $existingEntityId,
            'name' => 'Test Task',
            'description' => 'Task for saving'
        ]);
        
        // Mock the entity loader service which is used to load the existing task by guid and can save it
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity);
        $this->mockEntityLoader->method('save')->willReturn($existingEntityId);        

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn($daclDetails);
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);

        // Specify the entity_id here so we will save the existing entity
        $request->setBody(json_encode(['obj_type' => 'task', 'entity_id' => $existingEntityId]));
        $response = $this->entityController->postSaveAction($request);
        $this->assertEquals([
            'obj_type' => 'task',
            'entity_id' => $existingEntityId,
            'name' => 'Test Task',
            'description' => 'Task for saving',
            'applied_dacl' => $daclDetails,
            'currentuser_permissions' => $daclPermissions
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors when saving an entity
     */
    public function testPostSaveActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->postSaveAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->postSaveAction($request);

        // It should return an error if no obj_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());

        // Mock the entity loader service that it will return a null value for entity        
        $this->mockEntityLoader->method('getEntityById')->willReturn(null);

        $existingEntityId = Uuid::uuid4()->toString();
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'entity_id' => $existingEntityId]));
        $response = $this->entityController->postSaveAction($request);

        // It should return an error if no entity definition is found
        $this->assertEquals(['error' => 'No entity found.', 'entity_id' => $existingEntityId], $response->getOutputBuffer());
    }

    /**
     * Test the deleting of entity
     */
    public function testPostRemoveAction()
    {
        $existingEntityId = Uuid::uuid4()->toString();
        
        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);        

        // Mock the entity loader service which is used to load the existing task by guid and can save it
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity);
        $this->mockEntityLoader->method('delete')->willReturn(true);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
                
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postRemoveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);

        // Specify the entity_id here so we can delete the existing entity
        $request->setBody(json_encode(['entity_id' => $existingEntityId]));
        $response = $this->entityController->postRemoveAction($request);

        // it should return the entity id that was being deleted
        $this->assertEquals([$existingEntityId], $response->getOutputBuffer());
    }

    /**
     * Test the deleting of multiple entities
     */
    public function testPostRemoveActionMultipleEntities()
    {
        $existingEntityId1 = Uuid::uuid4()->toString();
        $existingEntityId2 = Uuid::uuid4()->toString();
        $existingEntityId3 = Uuid::uuid4()->toString();
        
        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);
        
        // Mock the entity loader service which is used to load the existing task by guid and can save it
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity);
        $this->mockEntityLoader->method('delete')->willReturn(true);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postRemoveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);

        // Specify the entity_id here so we can delete the existing entity
        $request->setBody(json_encode(['entity_id' => [$existingEntityId1, $existingEntityId2, $existingEntityId3]]));
        $response = $this->entityController->postRemoveAction($request);

        // it should return the entity ids that were being deleted
        $this->assertEquals([$existingEntityId1, $existingEntityId2, $existingEntityId3], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors when deleting an entity
     */
    public function testPostRemoveActionCatchingErrors()
    {
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->postRemoveAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postRemoveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->postRemoveAction($request);

        // It should return an error if no entity_id is provided in the params
        $this->assertEquals(['error' => 'entity_id is a required param.'], $response->getOutputBuffer());

        $existingEntityId = Uuid::uuid4()->toString();
        $taskName = 'Task To Delete';

        // Create test task entity
        $mockTaskEntity = $this->createMock(TaskEntity::class);
        $mockTaskEntity->method('getName')->willReturn($taskName);
        
        // Mock the entity loader service which is used to load the existing task by guid and can save it
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity);
        $this->mockEntityLoader->method('delete')->willReturn(true);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);

        // Set that we will not allow the entity to be deleted so we can catch the error
        $mockDacl->method('isAllowed')->willReturn(false);        
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postRemoveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);

        // Specify the entity_id here so we can delete the existing entity
        $request->setBody(json_encode(['entity_id' => $existingEntityId]));
        $response = $this->entityController->postRemoveAction($request);

        // it should return the entity id that was being deleted
        $this->assertEquals(["error" => "You do not have permissions to delete this entity: $taskName"], $response->getOutputBuffer());
    }

    /**
     * Test the saving of entity grouping
     */
    public function testPostSaveGroupAction()
    {
        $groupId = Uuid::uuid4()->toString();
        $groupDetails = [
            "group_id" => $groupId,
            "name" => 'Test Group',
            "f_system" => true,
            "sort_order" => 1,
            "commit_id" => 1
        ];        
        
        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);

        // Mock the entity definition loader service which is used to entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        // Create the group for testing
        $mockEntityGroup = $this->createMock(Group::class);
        $mockEntityGroup->method('toArray')->willReturn($groupDetails);

        // Create the entity groupings for testing
        $mockEntityGroupings = $this->createMock(EntityGroupings::class);
        $mockEntityGroupings->method('getByGuidOrGroupId')->willReturn($mockEntityGroup);
        $mockEntityGroupings->method('toArray')->willReturn([$groupDetails]);
        

        // Mock the grouping loader service which is used to get the entity groupings
        $this->mockGroupingLoader->method('get')->willReturn($mockEntityGroupings);

        // Make sure postSaveGroupAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode([
            'obj_type' => 'task',
            'field_name' => 'group',
            'action' => 'edit',
            'group_id' => $groupId
        ]));
        $response = $this->entityController->postSaveGroupAction($request);
        $this->assertEquals($groupDetails, $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors when saving a group
     */
    public function testPostSaveGroupActionCatchingErrors()
    {
        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('isPrivate')->willReturn(false);

        // Mock the entity definition loader service which is used to entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->postRemoveAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postSaveGroupAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->postSaveGroupAction($request);

        // It should return an error if no obj_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());

        // Make sure postSaveGroupAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task']));
        $response = $this->entityController->postSaveGroupAction($request);

        // It should return an error if no field_name is provided in the params
        $this->assertEquals(['error' => 'field_name is a required param.'], $response->getOutputBuffer());

        // Make sure postSaveGroupAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'field_name' => 'groups']));
        $response = $this->entityController->postSaveGroupAction($request);

        // It should return an error if no action is provided in the params
        $this->assertEquals(['error' => 'action is a required param.'], $response->getOutputBuffer());

        // Make sure postSaveGroupAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'field_name' => 'groups', 'action' => 'edit']));
        $response = $this->entityController->postSaveGroupAction($request);

        // It should return an error if no group_id is provided
        $this->assertEquals(['error' => 'Edit action needs group id to update the group.'], $response->getOutputBuffer());
    }

    /**
     * Test the getting of entity grouping
     */
    public function testGetGroupingsAction()
    {
        $groupId = Uuid::uuid4()->toString();
        $groupDetails = [
            "group_id" => $groupId,
            "name" => 'Test Group',
            "f_system" => true,
            "sort_order" => 1,
            "commit_id" => 1
        ];
        
        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('isPrivate')->willReturn(false);

        // Mock the entity definition loader service which is used to entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        // Create the entity groupings for testing
        $mockEntityGroupings = $this->createMock(EntityGroupings::class);
        $mockEntityGroupings->method('toArray')->willReturn([$groupDetails]);

        // Mock the grouping loader service which is used to get the entity groupings
        $this->mockGroupingLoader->method('get')->willReturn($mockEntityGroupings);

        // Make sure getGetGroupingsAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'field_name' => 'group']));
        $response = $this->entityController->getGetGroupingsAction($request);
        $this->assertEquals([
            'obj_type' => 'task',
            'field_name' => 'group',
            'groups' => [$groupDetails]
        ], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors when getting the entity grouping
     */
    public function testGetGroupingsActionCatchingErrors()
    {   
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->postRemoveAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure getGetGroupingsAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->getGetGroupingsAction($request);

        // It should return an error if no entity_id is provided in the params
        $this->assertEquals(['error' => 'obj_type & field_name are required params.'], $response->getOutputBuffer());

        // Mock the grouping loader service that it will return value when getting groups
        $this->mockGroupingLoader->method('get')->willReturn(null);

        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('isPrivate')->willReturn(false);

        // Mock the entity definition loader service which is used to entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        // Make sure getGetGroupingsAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'field_name' => 'group']));
        $response = $this->entityController->getGetGroupingsAction($request);
        $this->assertEquals(['error' => 'No groupings found for specified obj_type and field.'], $response->getOutputBuffer());
    }

    /**
     * Test the getting of entity grouping
     */
    public function testGetGroupByObjTypeAction()
    {
        $groupId = Uuid::uuid4()->toString();
        $groupDetails = [
            "group_id" => $groupId,
            "name" => 'Test Group',
            "f_system" => true,
            "sort_order" => 1,
            "commit_id" => 1
        ];
        
        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('isPrivate')->willReturn(false);

        // Mock the entity definition loader service which is used to entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        // Create the entity groupings for testing
        $mockEntityGroupings = $this->createMock(EntityGroupings::class);
        $mockEntityGroupings->method('toArray')->willReturn([$groupDetails]);

        // Mock the grouping loader service which is used to get the entity groupings
        $this->mockGroupingLoader->method('getGroupings')->willReturn($mockEntityGroupings);

        // Make sure getGetGroupingsAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'field_name' => 'group']));
        $response = $this->entityController->getGetGroupByObjTypeAction($request);
        $this->assertEquals([$groupDetails], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors when getting the entity grouping by object type
     */
    public function testGetGroupByObjTypeActionCatchingErrors()
    {   
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->postRemoveAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure getGetGroupingsAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->getGetGroupingsAction($request);

        // It should return an error if no entity_id is provided in the params
        $this->assertEquals(['error' => 'obj_type & field_name are required params.'], $response->getOutputBuffer());

        // Mock the grouping loader service that it will return value when getting groups
        $this->mockGroupingLoader->method('get')->willReturn(null);

        // Create entity definition for testing
        $mockDefinition = $this->createMock(EntityDefinition::class);
        $mockDefinition->method('isPrivate')->willReturn(false);

        // Mock the entity definition loader service which is used to entity definition
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        // Make sure getGetGroupingsAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'field_name' => 'group']));
        $response = $this->entityController->getGetGroupingsAction($request);
        $this->assertEquals(['error' => 'No groupings found for specified obj_type and field.'], $response->getOutputBuffer());
    }

    /**
     * Test the editing of multiple entities
     */
    public function testPostMassEditAction()
    {
        $savedTaskEntityId1 = Uuid::uuid4()->toString();
        $taskDetails = [
            'obj_type' => 'task',
            'entity_id' => $savedTaskEntityId1,
            'name' => 'Test Task 1',
            'description' => 'Task for mass edit 1'
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

        // Create multiple test task entity
        $mockTaskEntity1 = $this->createMock(TaskEntity::class);
        $mockTaskEntity1->method('getName')->willReturn('Test Task 2');        
        $mockTaskEntity1->method('toArray')->willReturn($taskDetails);
        
        // Mock the entity loader service which is used to create a new entity and can save it
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity1);
        $this->mockEntityLoader->method('save')->willReturn($savedTaskEntityId1);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn($daclDetails);
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task', 'entity_data' => $taskDetails, 'entity_id' => [$savedTaskEntityId1]]));
        $response = $this->entityController->postMassEditAction($request);
        $this->assertEquals([$taskDetails], $response->getOutputBuffer());
    }
    
    /**
     * Catch the possible errors when editing multiple entities
     */
    public function testPostMassEditActionCatchingErrors()
    {
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->postRemoveAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postMassEditAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->postMassEditAction($request);

        // It should return an error if no entity_id is provided in the params
        $this->assertEquals(['error' => 'entity_id is a required param.'], $response->getOutputBuffer());

        // Make sure postMassEditAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['entity_id' => Uuid::uuid4()->toString()]));
        $response = $this->entityController->postMassEditAction($request);

        // It should return an error if no entity_data is provided in the params
        $this->assertEquals(['error' => 'entity_data is a required param.'], $response->getOutputBuffer());

        // Make sure postMassEditAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['entity_id' => 'invalid entity id', 'entity_data' => ['name' => 'Test Task']]));
        $response = $this->entityController->postMassEditAction($request);

        // It should return an error if invalid entity id is being provided
        $this->assertEquals(['error' => 'Invalid entity_id was provided during mass edit action: invalid entity id.'], $response->getOutputBuffer());
    }

    /**
     * Test the updating the sort order of entities
     */
    public function testPostUpdateSortOrderEntitiesAction()
    {
        $taskEntityId1 = Uuid::uuid4()->toString();        
        $taskDetails = [
            'obj_type' => 'task',
            'entity_id' => $taskEntityId1,
            'name' => 'Test Task 1',
            'description' => 'Task for mass edit 1',
            'sort_order' => 123
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

        // Create multiple test task entity
        $mockTaskEntity1 = $this->createMock(TaskEntity::class);
        $mockTaskEntity1->method('getName')->willReturn('Test Task 2');        
        $mockTaskEntity1->method('toArray')->willReturn($taskDetails);
        
        // Mock the entity loader service which is used to create a new entity and can save it
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockTaskEntity1);
        $this->mockEntityLoader->method('save')->willReturn($taskEntityId1);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn($daclDetails);
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postUpdateSortOrderEntitiesAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['entity_ids' => [$taskEntityId1]]));
        $response = $this->entityController->postUpdateSortOrderEntitiesAction($request);
        $this->assertEquals([$taskDetails], $response->getOutputBuffer());
    }
}
