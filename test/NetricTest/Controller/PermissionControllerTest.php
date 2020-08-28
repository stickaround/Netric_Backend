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
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityGroupings\GroupingLoaderFactory;

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

    /**
     * Entity loader to get groupings
     *
     * @var GroupingLoader
     */
    private $groupingLoader = null;

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Get the service manager of the current user
        $this->serviceManager = $this->account->getServiceManager();
        $this->groupingLoader = $this->serviceManager->get(GroupingLoaderFactory::class);

        // Create the controller
        $this->controller = new PermissionController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
    {
        // Cleanup any test entities
        $loader = $this->serviceManager->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, $this->account->getAuthenticatedUser());
        }

        // Cleanup any test definitions
        $definitionLoader = $this->serviceManager->get(EntityDefinitionDataMapperFactory::class);
        foreach ($this->testDefinitions as $definition) {
            $definitionLoader->delete($definition);
        }
    }

    public function testGetGetDaclForEntityActionForObjTypeOnly()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('id', "");
        $req->setParam('obj_type', ObjectTypes::NOTE);

        $ret = $this->controller->getGetDaclForEntityAction();

        // Get creator owner group to test
        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups');
        $creatorGroup = $userGroups->getByName(UserEntity::GROUP_CREATOROWNER);

        // We should get the default dacl data for this object type
        $this->assertNotNull($ret);
        $this->assertArrayHasKey(Dacl::PERM_VIEW, $ret['entries']);
        $this->assertEquals(Dacl::PERM_VIEW, $ret['entries'][Dacl::PERM_VIEW]['name']);
        $this->assertTrue(in_array($creatorGroup->getGroupId(), $ret['entries'][Dacl::PERM_VIEW]['groups']));
    }

    public function testGetGetDaclForEntityAction()
    {
        // Create a task entity so we can get the default dacl for an entity
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);
        $taskEntity = $entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $taskEntity->setValue("name", "UnitTestTask");
        $entityLoader->save($taskEntity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $taskEntity;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', "task");
        $req->setParam('id', $taskEntity->getEntityId());

        $ret = $this->controller->getGetDaclForEntityAction();

        // Get creator owner group to test
        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups');
        $creatorGroup = $userGroups->getByName(UserEntity::GROUP_CREATOROWNER);
        $adminGroup = $userGroups->getByName(UserEntity::GROUP_ADMINISTRATORS);

        // Should get default dacl for this entity since we did not set any dacl yet
        $this->assertNotNull($ret);
        $this->assertArrayHasKey(Dacl::PERM_VIEW, $ret['entries']);
        $this->assertEquals(Dacl::PERM_VIEW, $ret['entries'][Dacl::PERM_VIEW]['name']);
        $this->assertTrue(in_array($creatorGroup->getGroupId(), $ret['entries'][Dacl::PERM_VIEW]['groups']));

        // Make sure that creator owner and administrators names are set in group_names
        $this->assertEquals($ret['group_names'][$creatorGroup->getGroupId()], $creatorGroup->getName());
        $this->assertEquals($ret['group_names'][$adminGroup->getGroupId()], $adminGroup->getName());
    }

    public function testGetGetDaclForEntityActionForObjType()
    {
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);

        // Make a new user and add them to the entity dacl
        $user = $entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user->setValue("name", "utest-dacl-entity-user");
        $entityLoader->save($user, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $user;

        // Set up the dacl and allow the user
        $dacl = new Dacl();
        $dacl->allowUser($user->getEntityId());

        // Set the dacl of the entity definition
        $defLoader = $this->serviceManager->get(EntityDefinitionLoaderFactory::class);
        $def = $defLoader->get(ObjectTypes::PRODUCT, $this->account->getAccountId());
        $def->setDacl($dacl);

        // Save the entity definition
        $definitionDatamapper = $this->serviceManager->get(EntityDefinitionDataMapperFactory::class);
        $definitionDatamapper->save($def);

        // Create a utest entity so we can get the dacl for the obj type
        $utestEntity = $entityLoader->create(ObjectTypes::PRODUCT, $this->account->getAccountId());
        $entityLoader->save($utestEntity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $utestEntity;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', ObjectTypes::PRODUCT);
        $req->setParam('entity_id', $utestEntity->getEntityId());

        $ret = $this->controller->getGetDaclForEntityAction();

        // Should get objtype dacl for this entity
        $this->assertNotNull($ret);
        $this->assertTrue(in_array($user->getEntityId(), $ret['entries'][Dacl::PERM_VIEW]['users']), var_export($ret, true));
        $this->assertEquals($ret['user_names'][$user->getEntityId()], $user->getName());
    }

    public function testPostSaveDaclEntriesAction()
    {
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);

        // Make a new user and add them to the entity dacl
        $user = $entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user->setValue("name", "utest-dacl-entity-user");
        $entityLoader->save($user, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $user;

        // Create a task entity to set the dacl
        $taskEntity = $entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $taskEntity->setValue("name", "UnitTestTaskDacl");
        $entityLoader->save($taskEntity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $taskEntity;

        // Set up the dacl and allow the user
        $dacl = new Dacl();
        $dacl->allowUser($user->getEntityId());

        // Set params in the request
        $data = $dacl->toArray();
        $data['obj_type'] = ObjectTypes::TASK;
        $data['entity_id'] = $taskEntity->getEntityId();

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postSaveDaclEntriesAction();

        // Should get default dacl for this entity since we did not set any dacl yet
        $this->assertNotNull($ret);
        $this->assertTrue(in_array($user->getEntityId(), $ret['entries'][Dacl::PERM_VIEW]['users']));

        // Get the task entity and check if the dacl was saved
        $daclLoader = $this->serviceManager->get(DaclLoaderFactory::class);
        $entity = $entityLoader->getEntityById($taskEntity->getEntityId(), $this->account->getAccountId());
        $daclEntity = $daclLoader->getForEntity($entity, $user);
        $this->assertTrue($daclEntity->isAllowed($user, Dacl::PERM_VIEW));
    }
}
