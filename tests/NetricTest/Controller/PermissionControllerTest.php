<?php

/**
 * Test the permission controller
 */
namespace NetricTest\Controller;

use Netric\Controller\PermissionController;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\ObjType\UserEntity;
use Netric\Permissions\Dacl;
use Netric\Permissions\Dacl\Entry;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory as EntityDefinitionDataMapperFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class PermissionControllerTest extends TestCase
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
     * Test entites that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Test definitions that should be cleaned up on tearDown
     *
     * @var EntityDefinitionInterface[]
     */
    private $testDefinitions = [];

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Get the service manager of the current user
        $this->serviceManager = $this->account->getServiceManager();

        // Create the controller
        $this->controller = new PermissionController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown()
    {
        // Cleanup any test entities
        $loader = $this->serviceManager->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, true);
        }

        // Cleanup any test definitions
        $definitionLoader = $this->serviceManager->get(EntityDefinitionDataMapperFactory::class);
        foreach ($this->testDefinitions as $definition) {
            $definitionLoader->delete($definition);
        }
    }

    public function testGetGetDaclForEntityAction()
    {
        // Create a task entity so we can get the default dacl for an entity
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);
        $taskEntity = $entityLoader->create("task");
        $taskEntity->setValue("name", "UnitTestTask");
        $entityLoader->save($taskEntity);
        $this->testEntities[] = $taskEntity;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', "task");
        $req->setParam('id', $taskEntity->getId());

        $ret = $this->controller->getGetDaclForEntityAction();

        // Should get default dacl for this entity since we did not set any dacl yet
        $this->assertNotNull($ret);
        $this->assertArrayHasKey(Dacl::PERM_VIEW, $ret['dacl']['entries']);
        $this->assertEquals(Dacl::PERM_VIEW, $ret['dacl']['entries'][Dacl::PERM_VIEW]['name']);
        $this->assertTrue(in_array(UserEntity::GROUP_CREATOROWNER, $ret['dacl']['entries'][Dacl::PERM_VIEW]['groups']));
    }

    public function testGetGetDaclForEntityActionForObjType()
    {
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);

        // Make a new user and add them to the entity dacl
        $user = $entityLoader->create("user");
        $user->setValue("name", "utest-dacl-entity");
        $entityLoader->save($user);
        $this->testEntities[] = $user;

        // Set up the dacl and allow the user
        $dacl = new Dacl();
        $dacl->allowUser($user->getId());

        $def = new EntityDefinition("unitTestDaclObjtype");
        $def->setDacl($dacl);

        // Save the entity definition
        $definitionDatamapper = $this->serviceManager->get(EntityDefinitionDataMapperFactory::class);
        $definitionDatamapper->save($def);
        $this->testDefinitions[] = $def;

        // Create a utest entity so we can get the dacl for the obj type
        $utestEntity = $entityLoader->create("unitTestDaclObjtype");
        $entityLoader->save($utestEntity);
        $this->testEntities[] = $utestEntity;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', "unitTestDaclObjtype");
        $req->setParam('id', $utestEntity->getId());

        $ret = $this->controller->getGetDaclForEntityAction();

        // Should get objtype dacl for this entity
        $this->assertNotNull($ret);
        $this->assertTrue(in_array($user->getId(), $ret['dacl']['entries'][Dacl::PERM_VIEW]['users']));
        $this->assertEquals($ret['user_names'][$user->getId()], $user->getName());
    }

    public function testPostSaveDaclEntriesAction()
    {
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);

        // Make a new user and add them to the entity dacl        
        $user = $entityLoader->create("user");
        $user->setValue("name", "utest-dacl-entity");        
        $entityLoader->save($user);
        $this->testEntities[] = $user;

        // Create a task entity to set the dacl
        $taskEntity = $entityLoader->create("task");
        $taskEntity->setValue("name", "UnitTestTaskDacl");
        $entityLoader->save($taskEntity);
        $this->testEntities[] = $taskEntity;

        // Set up the dacl and allow the user
        $dacl = new Dacl();
        $dacl->allowUser($user->getId());

        // Set params in the request
        $data = $dacl->toArray();
        $data['obj_type'] = "task";
        $data['entity_id'] = $taskEntity->getId();

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');
        
        $ret = $this->controller->postSaveDaclEntriesAction();

        // Should get default dacl for this entity since we did not set any dacl yet
        $this->assertNotNull($ret);
        $this->assertTrue(in_array($user->getId(), $ret['entries'][Dacl::PERM_VIEW]['users']));

        // Get the task entity and check if the dacl was saved
        $daclLoader = $this->serviceManager->get(DaclLoaderFactory::class);
        $entity = $entityLoader->get("task", $taskEntity->getId());
        $daclEntity = $daclLoader->getForEntity($entity);
        $this->assertTrue($dacl->isAllowed($user, Dacl::PERM_VIEW));
    }
}
