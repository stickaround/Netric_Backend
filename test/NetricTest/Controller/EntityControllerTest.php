<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Controller\EntityController;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityGroupings\Group;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Entity\BrowserView\BrowserViewService;
use Netric\Entity\Forms;
use Netric\Permissions\DaclLoader;
use Netric\Permissions\Dacl;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\Where;
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
        $mockAccount = $this->createStub(Account::class);
        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $accountContainer->method('loadById')->willReturn($mockAccount);

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

    public function testGetEntityAction()
    {
        $taskEntityId = Uuid::uuid4()->toString();
        $daclPermissions = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];

        // Create test task email
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
        $mockDacl->method('toArray')->willReturn([
            'entries' => [],
            'name' => 'task_dacl'
        ]);
        
        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure getAction is called and we get a response
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

    public function testGetEntityActionNoPermission()
    {
        $taskEntityId = Uuid::uuid4()->toString();

        // Set the view to false, since we can to test if the view permission is not allowed
        $daclPermissions = [
            'view' => false
        ];

        // Create test task email
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

        // Make sure getAction is called and we get a response
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

    public function testGetEntityActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->getGetAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure getAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->getGetAction($request);

        // It should return an error if no entity_id is provided in the params
        $this->assertEquals(['error' => 'entity_id or uname are required params.'], $response->getOutputBuffer());
    }

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
            'large' => 'Larg Forms'
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
                
        // Mock the entity loader service which is used to load the task by guid
        $this->mockEntityDefinitionLoader->method('get')->willReturn($mockDefinition);

        // Mock the forms service which is used to get the entity definition forms
        $this->mockForms->method('getDeviceForms')->willReturn($formDetails);
        
        // Mock the browser view service which is used to get the browser views for the user
        $this->mockBrowserViewService->method('getViewsForUser')->willReturn([]);
        $this->mockBrowserViewService->method('getDefaultViewForUser')->willReturn($browserViewDetails);

        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntityDefinition')->willReturn($mockDacl);
        
        // Make sure getAction is called and we get a response
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

    public function testDefinitionActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->entityController->getGetDefinitionAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure getAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->entityController->getGetDefinitionAction($request);

        // It should return an error if no entity_id is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());

        // Mock the entity loader service that it will return a null value for entity definition        
        $this->mockEntityDefinitionLoader->method('get')->willReturn(null);

        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => 'task']));
        $response = $this->entityController->getGetDefinitionAction($request);

        // It should return an error if no entity definition is found
        $this->assertEquals(['error' => 'task could not be loaded.'], $response->getOutputBuffer());
    }

    /*
    public function testGetGetEntityAction()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dashboardEntity = $loader->create(ObjectTypes::DASHBOARD, $this->account->getAccountId());
        $dashboardEntity->setValue("name", "activity-new");
        $loader->save($dashboardEntity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $dashboardEntity;

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode([
            'obj_type' => ObjectTypes::DASHBOARD,
            'entity_id' => $dashboardEntity->getEntityId()
        ]));

        $ret = $this->entityController->getGetAction();
        $this->assertEquals($dashboardEntity->getEntityId(), $ret['entity_id'], var_export($ret, true));
        $this->assertEquals($ret["currentuser_permissions"], ['view' => true, 'edit' => true, 'delete' => true]);
    }

    public function testPostGetEntityActionDashboardUname()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dashboardEntity = $loader->create(ObjectTypes::DASHBOARD, $this->account->getAccountId());
        $dashboardName = "activity-test" . uniqid();
        $dashboardEntity->setValue("name", $dashboardName);
        $dashboardEntity->setValue("owner_id", $this->account->getUser()->getEntityId());
        $loader->save($dashboardEntity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $dashboardEntity;

        // Test The getting of entity using unique name
        // Set params in the request
        $data = [
            'obj_type' => ObjectTypes::DASHBOARD,
            'uname' => $dashboardEntity->getValue("uname"),
            'uname_conditions' => [
                'owner_id' => $this->account->getUser()->getEntityId(),
            ],
        ];
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->entityController->postGetAction();
        $dashboardEntity = $loader->getEntityById($ret['entity_id'], $this->account->getAccountId());
        $this->assertEquals($dashboardEntity->getValue("name"), $dashboardName);
    }

    public function testPostGetEntityAction()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $customer = $loader->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer->setValue("name", "Test");
        $loader->save($customer, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $customer;

        $data = [
            'obj_type' => ObjectTypes::CONTACT,
            'entity_id' => $customer->getEntityId(),
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->entityController->postGetAction();
        $this->assertEquals($customer->getEntityId(), $ret['entity_id'], var_export($ret, true));
        $this->assertEquals($ret["currentuser_permissions"], ['view' => true, 'edit' => true, 'delete' => true]);
    }

    public function testPostGetEntityActionUname()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $site = $loader->create("cms_site", $this->account->getAccountId());
        $site->setValue("name", "www.testsite.com");
        $loader->save($site, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $site;

        $page = $loader->create("cms_page", $this->account->getAccountId());
        $page->setValue("name", "testPostGetEntityAction");
        $page->setValue("site_id", $site->getEntityId());
        $loader->save($page, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $page;

        $data = [
            'obj_type' => "cms_page",
            'uname' => $page->getValue("uname"),
            'uname_conditions' => [
                'site_id' => $site->getEntityId(),
            ],
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->entityController->postGetAction();
        $this->assertEquals($page->getEntityId(), $ret['entity_id'], var_export($ret, true));
        $this->assertEquals($ret["currentuser_permissions"], ['view' => true, 'edit' => true, 'delete' => true]);
    }
    
    public function testPostGetEntityActionGuid()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $site = $loader->create("cms_site", $this->account->getAccountId());
        $site->setValue("name", "www.testsite.com");
        $loader->save($site, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $site;

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode(['entity_id' => $site->getEntityId()]));
        $req->setParam('content-type', 'application/json');

        $ret = $this->entityController->postGetAction();
        $this->assertEquals($site->getEntityId(), $ret['entity_id'], var_export($ret, true));
        $this->assertEquals($ret["currentuser_permissions"], ['view' => true, 'edit' => true, 'delete' => true]);
    }

    public function testGetDefinitionForms()
    {
        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setParam('obj_type', ObjectTypes::CONTACT);

        $ret = $this->entityController->getGetDefinitionAction();

        // Make sure the small form was loaded
        $this->assertFalse(empty($ret['forms']['small']));

        // Make sure the large form was loaded
        $this->assertFalse(empty($ret['forms']['large']));
    }

    public function testSave()
    {
        $data = [
            'obj_type' => ObjectTypes::CONTACT,
            'first_name' => "Test",
            'last_name' => "User",
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->entityController->postSaveAction();

        $this->assertEquals($data['obj_type'], $ret['obj_type']);
        $this->assertEquals($data['first_name'], $ret['first_name']);
        $this->assertEquals($data['last_name'], $ret['last_name']);
        $this->assertEquals($ret["currentuser_permissions"], ['view' => true, 'edit' => true, 'delete' => true]);
    }

    public function testDelete()
    {
        // First create an entity to save
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $entity = $loader->create(ObjectTypes::NOTE, $this->account->getAccountId());
        $entity->setValue("name", "Test");
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        $dm->save($entity, $this->account->getAuthenticatedUser());
        $entityId = $entity->getEntityId();

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setParam("obj_type", ObjectTypes::NOTE);
        $req->setParam("entity_id", $entityId);

        // Try to delete
        $ret = $this->entityController->postRemoveAction();
        $this->assertEquals($entityId, $ret[0], var_export($ret, true));
    }

    public function testGetGroupings()
    {
        $req = $this->entityController->getRequest();
        $req->setParam("obj_type", ObjectTypes::CONTACT);
        $req->setParam("field_name", "groups");

        $ret = $this->entityController->getGetGroupingsAction();
        $this->assertFalse(isset($ret['error']));
        $this->assertTrue(count($ret) > 0);
    }

    public function testGetAllDefinitionsAction()
    {
        // Set params in the request
        $req = $this->entityController->getRequest();
        $ret = $this->entityController->getAllDefinitionsAction();

        // Try to get the task entity definition and we use it in our unit test
        $entityDefData = null;
        foreach ($ret as $defData) {
            if ($defData['obj_type'] === "task") {
                $entityDefData = $defData;
                break;
            }
        }

        $this->assertNotNull($entityDefData);
        $this->assertNotNull($entityDefData['obj_type']);

        // Make sure the small form was loaded
        $this->assertFalse(empty($entityDefData['forms']['small']));

        // Make sure the large form was loaded
        $this->assertFalse(empty($entityDefData['forms']['large']));
    }

    public function testUpdateEntityDefAction()
    {
        $objType = "unittest_customer";

        // Test creating new entity definition
        $data = [
            'obj_type' => $objType,
            'title' => "Unit Test Customer",
            'system' => false,
            'fields' => [
                "test_field" => [
                    'name' => "test_field",
                    'title' => "New Test Field",
                    'type' => "text",
                    'system' => false
                ]
            ]
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->entityController->postUpdateEntityDefAction();

        // Get the newly created entity definition
        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $testDef = $defLoader->get($objType, $this->account->getAccountId());
        $this->testDefinitions[] = $testDef;

        // Test that the new entity definition was created
        $this->assertEquals($testDef->id, $ret['id']);
        $this->assertEquals($testDef->getTitle(), "Unit Test Customer");
        $this->assertEquals($testDef->revision, 1);

        // Test the field created
        $this->assertNotNull($testDef->getField("test_field"));

        // Remove the custom test field added
        $data = [
            'id' => $testDef->id,
            'obj_type' => $objType,
            'deleted_fields' => ["test_field"]
        ];

        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->entityController->postUpdateEntityDefAction();

        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $deletedFieldDef = $defLoader->get($objType, $this->account->getAccountId());

        $this->assertNull($deletedFieldDef->getField("test_field"));
        $this->assertEquals($deletedFieldDef->revision, 2);

        // Test the updating of entity definition
        $data = [
            'id' => $testDef->getEntityDefinitionId(),
            'obj_type' => $objType,
            'title' => "Updated Definition Title",
        ];

        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->entityController->postUpdateEntityDefAction();

        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $updatedDef = $defLoader->get($objType, $this->account->getAccountId());

        $this->assertEquals($updatedDef->getTitle(), "Updated Definition Title");
        $this->assertEquals($updatedDef->revision, 3);
    }

    public function testPostDeleteEntityDefAction()
    {
        $objType = "unittest_customer";

        // Test creating new entity definition
        $data = [
            'obj_type' => $objType,
            'title' => "Unit Test Customer",
            'system' => false,
            'fields' => [
                "test_field" => [
                    'name' => "test_field",
                    'title' => "New Test Field",
                    'type' => "text",
                    'system' => false
                ]
            ]
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->entityController->postUpdateEntityDefAction();

        // Get the newly created entity definition
        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $testDef = $defLoader->get($objType, $this->account->getAccountId());
        $this->testDefinitions[] = $testDef;

        // Test that the new entity definition was created
        $this->assertEquals($testDef->getEntityDefinitionId(), $ret['id']);
        $this->assertEquals($testDef->getTitle(), "Unit Test Customer");
        $this->assertEquals($testDef->revision, 1);

        // Now Delete the newly created entity definition
        $data = [
            'obj_type' => $objType,
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->entityController->postDeleteEntityDefAction();
        $this->assertTrue($ret);
    }

    public function testMassEdit()
    {
        // Setup the loaders
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        $groupingsLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);

        $groupings = $groupingsLoader->get(ObjectTypes::NOTE . "/groups/" . $this->account->getUser()->getEntityId(), $this->account->getAccountId());

        $group1 = new Group();
        $group2 = new Group();
        $group1->setValue("name", "group1");
        $group2->setValue("name", "group2");
        $groupings->add($group1);
        $groupings->add($group2);
        $groupingsLoader->save($groupings);

        $this->testGroups[] = $group1->getGroupId();
        $this->testGroups[] = $group2->getGroupId();

        // First create entities to save
        $entity1 = $loader->create(ObjectTypes::NOTE, $this->account->getAccountId());
        $entity1->setValue("body", "Note 1");
        $entity1->addMultiValue("groups", $group1->getGroupId(), $group1->getName());
        $dm->save($entity1, $this->account->getAuthenticatedUser());
        $entityGuid1 = $entity1->getEntityId();
        $this->testEntities[] = $entity1;

        $entity2 = $loader->create(ObjectTypes::NOTE, $this->account->getAccountId());
        $entity2->setValue("body", "Note 2");
        $entity2->addMultiValue("groups", $group2->getGroupId(), $group2->getName());
        $dm->save($entity2, $this->account->getAuthenticatedUser());
        $entityGuid2 = $entity2->getEntityId();
        $this->testEntities[] = $entity2;

        $groupData[$group1->getGroupId()] = $group1->getName();
        $groupData[$group2->getGroupId()] = $group2->getName();

        // Setup the data
        $data = [
            'entity_id' => [$entityGuid1, $entityGuid2, "invalid-guid"],
            'entity_data' => [
                "body" => "test mass edit",
                "groups" => [$group1->getGroupId(), $group2->getGroupId()],
                "groups_fval" => $groupData
            ]
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->entityController->postMassEditAction();

        // Test the results
        $this->assertEquals(sizeof($ret), 3);
        $this->assertNotNull($ret["error"][0]);
        $this->assertEquals($data['entity_data']['body'], $ret[0]['body']);
        $this->assertEquals($data['entity_data']['body'], $ret[1]['body']);

        // TODO: Need to redo all the unit tests for mass edit regarding groups since we already moved to use the guid for groups
    }

    public function testMergeEntities()
    {
        // Setup the loaders
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // First create entities to merge
        $entity1 = $loader->create(ObjectTypes::NOTE, $this->account->getAccountId());
        $entity1->setValue("body", "body 1");
        $entity1->setValue("name", "name 1");
        $entity1->setValue("title", "title 1");
        $entity1->setValue("website", "website 1");
        $entity1->addMultiValue("groups", 1, "note group 1");
        $dm->save($entity1, $this->account->getAuthenticatedUser());
        $entityId1 = $entity1->getEntityId();

        $entity2 = $loader->create(ObjectTypes::NOTE, $this->account->getAccountId());
        $entity2->setValue("body", "body 2");
        $entity2->setValue("name", "name 2");
        $entity2->setValue("path", "path 2");
        $entity2->setValue("website", "website 2");
        $entity2->addMultiValue("groups", 2, "note group 2");
        $dm->save($entity2, $this->account->getAuthenticatedUser());
        $entityId2 = $entity2->getEntityId();

        $entity3 = $loader->create(ObjectTypes::NOTE, $this->account->getAccountId());
        $entity3->setValue("body", "body 3");
        $entity3->setValue("name", "name 3");
        $entity3->setValue("path", "path 3");
        $entity3->setValue("website", "website 3");
        $entity3->addMultiValue("groups", 3, "note group 3");
        $entity3->addMultiValue("groups", 33, "note group 33");
        $dm->save($entity3, $this->account->getAuthenticatedUser());
        $entityId3 = $entity3->getEntityId();

        // Setup the merge data
        $data = [
            'obj_type' => ObjectTypes::NOTE,
            'entity_id' => [$entityId1, $entityId2, $entityId3],
            'merge_data' => [
                $entityId1 => ["body"],
                $entityId2 => ["path", "website"],
                $entityId3 => ["groups", "name"],
            ]
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->entityController->postMergeEntitiesAction();

        // Test the results
        $this->assertFalse(empty($ret['entity_id']));
        $this->assertEquals($ret['body'], $entity1->getValue("body"));
        $this->assertEquals($ret['path'], $entity2->getValue("path"));
        $this->assertEquals($ret['website'], $entity2->getValue("website"));
        $this->assertEquals($ret['name'], $entity3->getValue("name"));
        $this->assertEquals($ret['groups'], $entity3->getValue("groups"));
        $this->assertEquals($ret['groups_fval'][3], $entity3->getValueName("groups", 3));
        $this->assertEquals($ret['groups_fval'][33], $entity3->getValueName("groups", 33));

        // Test that the entities that were merged have been moved
        $mId1 = $dm->checkEntityHasMoved($entity1->getDefinition(), $entityId1, $this->account->getAccountId());
        $this->assertEquals($mId1, $ret['entity_id']);

        $mId2 = $dm->checkEntityHasMoved($entity2->getDefinition(), $entityId2, $this->account->getAccountId());
        $this->assertEquals($mId2, $ret['entity_id']);

        $mId3 = $dm->checkEntityHasMoved($entity3->getDefinition(), $entityId3, $this->account->getAccountId());
        $this->assertEquals($mId3, $ret['entity_id']);

        // Lets load the actual entities and check if they are archived
        $originalEntity1 = $loader->getEntityById($entityId1, $this->account->getAccountId());
        $this->assertTrue($originalEntity1->isArchived());

        $originalEntity2 = $loader->getEntityById($entityId2, $this->account->getAccountId());
        $this->assertTrue($originalEntity2->isArchived());

        $originalEntity3 = $loader->getEntityById($entityId3, $this->account->getAccountId());
        $this->assertTrue($originalEntity3->isArchived());
    }

    public function testSaveGroup()
    {
        // Setup the save group data
        $dataGroup = [
            'action' => "add",
            'obj_type' => ObjectTypes::NOTE,
            'field_name' => 'groups',
            'name' => 'test save group',
            'color' => 'blue'
        ];

        // Set params in the request
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode($dataGroup));
        $retGroup = $this->entityController->postSaveGroupAction();

        $this->assertNotEmpty($retGroup['group_id']);
        $this->assertEquals($retGroup['name'], $dataGroup['name']);
        $this->assertEquals($retGroup['color'], $dataGroup['color']);
    }

    public function testDeleteEntityDef()
    {
        $objType = "UnitTestObjType";
        $def = new EntityDefinition($objType, $this->account->getAccountId());
        $def->setSystem(false);

        // Save the entity definition
        $dataMapper = $this->account->getServiceManager()->get(EntityDefinitionDataMapperFactory::class);
        $dataMapper->save($def);

        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $testDef = $defLoader->get($objType, $this->account->getAccountId());
        $this->testDefinitions[] = $testDef;

        $result = $dataMapper->delete($testDef);
        $this->assertEquals($result, true);
    }

    public function testGetDefinitionActionToReturnError()
    {
        // Set obj_type that is currently not existing
        $req = $this->entityController->getRequest();
        $req->setParam('obj_type', 'NonExistingDefinition');
        $ret = $this->entityController->getGetDefinitionAction();

        // Test that error was being returned
        $this->assertNotEmpty($ret['error']);
    }

    public function testGetActionToReturnError()
    {
        $req = $this->entityController->getRequest();

        // Setting an empty guid should return an error
        $req->setBody(json_encode(['entity_id' => '']));
        $ret = $this->entityController->getGetAction();
        $this->assertNotEmpty($ret['error']);

        // Setting a object type only should return an error
        $req->setBody(json_encode(['obj_type' => ObjectTypes::TASK]));
        $ret = $this->entityController->getGetAction();
        $this->assertNotEmpty($ret['error']);

        // Setting empty uname should return an error
        $req->setBody(json_encode(['uname' => '']));
        $ret = $this->entityController->getGetAction();
        $this->assertNotEmpty($ret['error']);
    }

    public function testPostDeleteEntityDefActionToReturnError()
    {
        // Deleting an entity definition without providing an object type should return an error
        $req = $this->entityController->getRequest();
        $req->setBody(json_encode(['name' => 'DefWithNoType']));
        $ret = $this->entityController->postDeleteEntityDefAction();
        $this->assertEquals($ret['error'], 'obj_type is a required param');
    }

    public function testPostUpdateEntityDefActionToReturnError()
    {
        $req = $this->entityController->getRequest();

        // Saving an entity definition without providing an object type should return an error
        $req->setBody(json_encode(['name' => 'DefWithNoType']));
        $ret = $this->entityController->postUpdateEntityDefAction();
        $this->assertEquals($ret['error'], 'obj_type is a required param');

        // Saving an entity definition with an empty obj_type should return an error
        $req->setBody(json_encode(['obj_type' => '', 'name' => 'DefWithNoType']));
        $ret = $this->entityController->postUpdateEntityDefAction();
        $this->assertEquals($ret['error'], 'obj_type is empty.');

        // Saving an existing entity definition but with non existing object type
        $req->setBody(json_encode(['obj_type' => 'NonExistingDefinition', 'id' => 1]));
        $ret = $this->entityController->postUpdateEntityDefAction();
        $this->assertNotEmpty($ret['error']);
    }

    public function testPostSaveActionToReturnError()
    {
        $req = $this->entityController->getRequest();

        // Saving an entity without providing any data should return an error
        $req->setBody('');
        $ret = $this->entityController->putSaveAction();
        $this->assertNotEmpty($ret['error']);

        // Saving an entity with invalid id should return an error
        $req->setBody(json_encode(['obj_type' => ObjectTypes::TASK, 'entity_id' => 'invalidId123']));
        $ret = $this->entityController->postSaveAction();
        $this->assertNotEmpty($ret['error']);
    }

    public function testGetRemoveActionToReturnError()
    {
        $req = $this->entityController->getRequest();

        // Removing an entity with an empty object type should return an error
        $req->setParam('obj_type', '');
        $ret = $this->entityController->getRemoveAction();
        $this->assertEquals($ret['error'], 'obj_type is a required param', var_export($ret, true));
    }

    public function testPostSaveGroupActionToReturnError()
    {
        $req = $this->entityController->getRequest();

        // Saving a group without object type should return an error
        $req->setBody(json_encode(['field_name' => 'group']));
        $ret = $this->entityController->postSaveGroupAction();
        $this->assertEquals($ret['error'], 'obj_type is a required param');

        // Saving a group without field name should return an error
        $req->setBody(json_encode(['obj_type' => ObjectTypes::TASK]));
        $ret = $this->entityController->postSaveGroupAction();
        $this->assertEquals($ret['error'], 'field_name is a required param');

        // Saving a group without an action should return an error
        $req->setBody(json_encode(['obj_type' => ObjectTypes::TASK, 'field_name' => 'group']));
        $ret = $this->entityController->postSaveGroupAction();
        $this->assertEquals($ret['error'], 'action is a required param');
    }

    public function testAccessEntityWithUserThatHasNoPermission()
    {
        // Create a task entity that that can be retrieved by a user that has no permission
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $taskEntity = $loader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $taskEntity->setValue("name", "Test Task");
        $loader->save($taskEntity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $taskEntity;

        $userEntity = $loader->create(ObjectTypes::USER, $this->account->getAccountId());
        $userEntity->setValue("name", "Test User");
        $loader->save($userEntity, $this->account->getSystemUser());
        $this->testEntities[] = $userEntity;

        $account = Bootstrap::getAccount();
        $account->setCurrentUser($userEntity);

        // Create the controller
        $controller = new EntityController($this->account->getApplication(), $account);
        $controller->testMode = true;

        // Set params in the request
        $req = $controller->getRequest();
        $req->setBody(json_encode([
            'obj_type' => ObjectTypes::TASK,
            'entity_id' => $taskEntity->getEntityId()
        ]));

        $ret = $controller->getGetAction();

        // Since we have no permission to view the task, it should return an error message.
        $this->assertEquals(count($ret), 3);
        $this->assertEquals($taskEntity->getEntityId(), $ret['entity_id'], var_export($ret, true));
        $this->assertEquals($ret["error"], "You do not have permission to view this.");

        // Let's try to update the task using the user that has no permission
        $data = $taskEntity->toArray();
        $data['name'] = 'Updated task name';

        // Set params in the request
        $req = $controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $controller->postSaveAction();

        // Since we have no permission to edit the task, it should return an error message.
        $this->assertEquals(count($ret), 3);
        $this->assertEquals($taskEntity->getEntityId(), $ret['entity_id'], var_export($ret, true));
        $this->assertEquals($ret["error"], "You do not have permission to edit this.");
    }

    public function testPostUpdateSortOrderEntities()
    {        
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
     
        // Create the tasks that will be sorted
        $taskEntity1 = $loader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $taskEntity1->setValue("name", "Test Task 1");
        $loader->save($taskEntity1, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $taskEntity1;

        // Set a 1 second sleep so the setting of sort_order for other tasks will be different
        sleep(1);

        $taskEntity2 = $loader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $taskEntity2->setValue("name", "Test Task 2");
        $loader->save($taskEntity2, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $taskEntity2;

        sleep(1);

        $taskEntity3 = $loader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $taskEntity3->setValue("name", "Test Task 3");
        $loader->save($taskEntity3, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $taskEntity3;

        sleep(1);
        
        $taskEntity4 = $loader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $taskEntity4->setValue("name", "Test Task 4");
        $loader->save($taskEntity4, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $taskEntity4;

        // Check here first the sort orders of the tasks
        $this->assertGreaterThan($taskEntity1->getValue("sort_order"), $taskEntity2->getValue("sort_order"));
        $this->assertGreaterThan($taskEntity3->getValue("sort_order"), $taskEntity4->getValue("sort_order"));

        // Let's try to update sort order of the tasks
        $data['entity_ids'] = [ $taskEntity2->getEntityId(), $taskEntity4->getEntityId(), $taskEntity1->getEntityId(), $taskEntity3->getEntityId() ];
        
        // Create the controller
        $controller = new EntityController($this->account->getApplication(), $this->account);
        $controller->testMode = true;

        // Set params in the request
        $req = $controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $controller->postUpdateSortOrderEntitiesAction();
        
        // Check the sort order of the tasks
        $this->assertEquals($ret[0]["entity_id"], $taskEntity2->getEntityId());
        $this->assertEquals($ret[1]["entity_id"], $taskEntity4->getEntityId());
        $this->assertEquals($ret[2]["entity_id"], $taskEntity1->getEntityId());
        $this->assertEquals($ret[3]["entity_id"], $taskEntity3->getEntityId());

        // Make sure that sort orders were updated
        $this->assertGreaterThan($ret[3]["sort_order"], $ret[2]["sort_order"]);
        $this->assertGreaterThan($ret[1]["sort_order"], $ret[0]["sort_order"]);

        // Make sure that the sort order was changed
        $this->assertNotEquals($ret[0]["sort_order"], $taskEntity2->getValue('sort_order'));
    }

    */
}
