<?php

/**
 * Test the entity controller
 */
namespace NetricTest\Controller;

use Netric\EntityDefinition\DataMapper\DataMapperFactory as EntityDefinitionDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Controller\EntityController;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use PHPUnit\Framework\TestCase;

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
    private $testGroups = array();

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

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Create the controller
        $this->controller = new EntityController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown()
    {
        // Delete the added groups
        foreach ($this->testGroups as $groupId) {
            $dataRemove = array(
                'action' => "delete",
                'obj_type' => "note",
                'field_name' => 'groups',
                'id' => $groupId,
                'filter' => array('user_id' => -9)
            );

            // Set params in the request
            $req = $this->controller->getRequest();
            $req->setBody(json_encode($dataRemove));
            $this->controller->postSaveGroupAction();
        }

        // Cleanup any test entities
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, true);
        }

        // Cleanup any test entity definitions
        $dataMapper = $this->account->getServiceManager()->get(EntityDefinitionDataMapperFactory::class);
        foreach ($this->testDefinitions as $def) {
            $dataMapper->delete($def);
        }
    }

    public function testGetGetEntityAction()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dashboardEntity = $loader->create("dashboard");
        $dashboardEntity->setValue("name", "activity");
        $loader->save($dashboardEntity);
        $this->testEntities[] = $dashboardEntity;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', 'dashboard');
        $req->setParam('id', $dashboardEntity->getId());

        $ret = $this->controller->getGetAction();
        $this->assertEquals($dashboardEntity->getId(), $ret['id'], var_export($ret, true));
    }

    public function testPostGetEntityActionDashboardUname()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dashboardEntity = $loader->create("dashboard");
        $dashboardEntity->setValue("name", "activity");
        $dashboardEntity->setValue("owner_id", $this->account->getUser()->getId());
        $loader->save($dashboardEntity);
        $this->testEntities[] = $dashboardEntity;

        // Test The getting of entity using unique name
        // Set params in the request
        $data = array(
            'obj_type' => "dashboard",
            'uname' => $dashboardEntity->getValue("uname"),
            'uname_conditions' => [
                'owner_id' => $this->account->getUser()->getId(),
            ],
        );
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postGetAction();
        $dashboardEntity = $loader->get("dashboard", $ret['id']);
        $this->assertEquals($dashboardEntity->getValue("name"), "activity");
    }

    public function testPostGetEntityAction()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $customer = $loader->create("customer");
        $customer->setValue("name", "Test");
        $loader->save($customer);
        $this->testEntities[] = $customer;

        $data = array(
            'obj_type' => "customer",
            'id' => $customer->getId(),
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postGetAction();
        $this->assertEquals($customer->getId(), $ret['id'], var_export($ret, true));

    }

    public function testPostGetEntityActionUname()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $site = $loader->create("cms_site");
        $site->setValue("name", "www.testsite.com");
        $loader->save($site);
        $this->testEntities[] = $site;

        $page = $loader->create("cms_page");
        $page->setValue("name", "testPostGetEntityAction");
        $page->setValue("site_id", $site->getId());
        $loader->save($page);
        $this->testEntities[] = $page;

        $data = array(
            'obj_type' => "cms_page",
            'uname' => $page->getValue("uname"),
            'uname_conditions' => [
                'site_id' => $site->getId(),
            ],
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postGetAction();
        $this->assertEquals($page->getId(), $ret['id'], var_export($ret, true));

    }

    public function testGetDefinitionForms()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', "customer");

        $ret = $this->controller->getGetDefinitionAction();

        // Make sure the small form was loaded
        $this->assertFalse(empty($ret['forms']['small']));

        // Make sure the large form was loaded
        $this->assertFalse(empty($ret['forms']['large']));
    }

    public function testSave()
    {
        $data = array(
            'obj_type' => "customer",
            'first_name' => "Test",
            'last_name' => "User",
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->controller->postSaveAction();

        $this->assertEquals($data['obj_type'], $ret['obj_type'], $ret);
        $this->assertEquals($data['first_name'], $ret['first_name'], $ret);
        $this->assertEquals($data['last_name'], $ret['last_name'], $ret);
    }

    public function testDelete()
    {
        // First create an entity to save
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $entity = $loader->create("note");
        $entity->setValue("name", "Test");
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        $dm->save($entity);
        $entityId = $entity->getId();

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("obj_type", "note");
        $req->setParam("id", $entityId);

        // Try to delete
        $ret = $this->controller->postRemoveAction();
        $this->assertEquals($entityId, $ret[0]);
    }

    public function testGetGroupings()
    {
        $req = $this->controller->getRequest();
        $req->setParam("obj_type", "customer");
        $req->setParam("field_name", "groups");

        $ret = $this->controller->getGetGroupingsAction();
        $this->assertFalse(isset($ret['error']));
        $this->assertTrue(count($ret) > 0);
    }

    public function testSavePendingObjectMultiObjects()
    {
        $data = array(
            'obj_type' => "calendar_event",
            'name' => "Test",
            'attendees_new' => [
                ['name' => '[user:1:test]'],
                ['name' => '[user:2:test2]']
            ],
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->controller->postSaveAction();

        $this->assertNotNull($ret['attendees'][0]);
        $this->assertNotNull($ret['attendees'][1]);
        $this->assertEquals($data['attendees_new'][0]['name'], $ret['attendees_fval'][$ret['attendees'][0]]);
        $this->assertEquals($data['attendees_new'][1]['name'], $ret['attendees_fval'][$ret['attendees'][1]]);
    }

    public function testGetAllDefinitionsAction()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $ret = $this->controller->getAllDefinitionsAction();

        $this->assertTrue($ret[0]['id'] > 0);

        // Make sure the small form was loaded
        $this->assertFalse(empty($ret[0]['forms']['small']));

        // Make sure the large form was loaded
        $this->assertFalse(empty($ret[0]['forms']['large']));
    }

    public function testUpdateEntityDefAction()
    {
        $objType = "unittest_customer";

        // Test creating new entity definition
        $data = array(
            'obj_type' => $objType,
            'title' => "Unit Test Customer",
            'system' => false,
            'fields' => array(
                "test_field" => array(
                    'name' => "test_field",
                    'title' => "New Test Field",
                    'type' => "text",
                    'system' => false
                )
            )
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->controller->postUpdateEntityDefAction();

        // Get the newly created entity definition
        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $testDef = $defLoader->get($objType);
        $this->testDefinitions[] = $testDef;

        // Test that the new entity definition was created
        $this->assertEquals($testDef->id, $ret['id']);
        $this->assertEquals($testDef->getTitle(), "Unit Test Customer");
        $this->assertEquals($testDef->revision, 1);

        // Test the field created
        $this->assertNotNull($testDef->getField("test_field"));

        // Remove the custom test field added
        $data = array(
            'id' => $testDef->id,
            'obj_type' => $objType,
            'deleted_fields' => array("test_field")
        );

        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->controller->postUpdateEntityDefAction();

        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $deletedFieldDef = $defLoader->get($objType);

        $this->assertNull($deletedFieldDef->getField("test_field"));
        $this->assertEquals($deletedFieldDef->revision, 2);

        // Test the updating of entity definition
        $data = array(
            'id' => $testDef->id,
            'obj_type' => $objType,
            'title' => "Updated Definition Title",
        );

        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->controller->postUpdateEntityDefAction();

        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $updatedDef = $defLoader->get($objType);

        $this->assertEquals($updatedDef->getTitle(), "Updated Definition Title");
        $this->assertEquals($updatedDef->revision, 3);
    }

    public function testMassEdit()
    {
        // Setup the loaders
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        // First create entities to save
        $entity1 = $loader->create("note");
        $entity1->setValue("body", "Note 1");
        $entity1->addMultiValue("groups", 1, "note group 1");
        $dm->save($entity1);
        $entityId1 = $entity1->getId();

        $entity2 = $loader->create("note");
        $entity2->setValue("body", "Note 2");
        $entity2->addMultiValue("groups", 2, "note group 2");
        $dm->save($entity2);
        $entityId2 = $entity2->getId();

        // Setup the data
        $data = array(
            'obj_type' => "note",
            'id' => array($entityId1, $entityId2),
            'entity_data' => array(
                "body" => "test mass edit",
                "groups" => array(3, 4),
                "groups_fval" => array(3 => "mass edit group 1", 4 => "mass edit group 2")
            )
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->controller->postMassEditAction();

        // Test the results
        $this->assertEquals(sizeof($ret), 2);
        $this->assertEquals($data['entity_data']['body'], $ret[0]['body']);
        $this->assertEquals($data['entity_data']['body'], $ret[1]['body']);

        // Test that the groups were updated
        $this->assertTrue(in_array($data['entity_data']['groups'][0], $ret[0]['groups']));
        $this->assertTrue(in_array($data['entity_data']['groups'][1], $ret[0]['groups']));

        $this->assertTrue(in_array($data['entity_data']['groups'][0], $ret[1]['groups']));
        $this->assertTrue(in_array($data['entity_data']['groups'][1], $ret[1]['groups']));


        // Lets load the actual entities and test them
        $updatedEntity1 = $loader->get("note", $entityId1);
        $this->assertEquals($data['entity_data']['body'], $updatedEntity1->getValue("body"));
        $this->assertTrue(in_array($data['entity_data']['groups'][0], $updatedEntity1->getValue("groups")));
        $this->assertTrue(in_array($data['entity_data']['groups'][1], $updatedEntity1->getValue("groups")));

        // Lets the the value name of the groups
        $this->assertEquals($data['entity_data']['groups_fval'][3], $updatedEntity1->getValueName("groups", 3));
        $this->assertEquals($data['entity_data']['groups_fval'][4], $updatedEntity1->getValueName("groups", 4));

        $updatedEntity2 = $loader->get("note", $entityId2);
        $this->assertEquals($data['entity_data']['body'], $updatedEntity2->getValue("body"));
        $this->assertTrue(in_array($data['entity_data']['groups'][0], $updatedEntity2->getValue("groups")));
        $this->assertTrue(in_array($data['entity_data']['groups'][1], $updatedEntity2->getValue("groups")));

        // Lets the the value name of the groups
        $this->assertEquals($data['entity_data']['groups_fval'][3], $updatedEntity2->getValueName("groups", 3));
        $this->assertEquals($data['entity_data']['groups_fval'][4], $updatedEntity2->getValueName("groups", 4));
    }

    public function testMergeEntities()
    {
        // Setup the loaders
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        // First create entities to merge
        $entity1 = $loader->create("note");
        $entity1->setValue("body", "body 1");
        $entity1->setValue("name", "name 1");
        $entity1->setValue("title", "title 1");
        $entity1->setValue("website", "website 1");
        $entity1->addMultiValue("groups", 1, "note group 1");
        $dm->save($entity1);
        $entityId1 = $entity1->getId();

        $entity2 = $loader->create("note");
        $entity2->setValue("body", "body 2");
        $entity2->setValue("name", "name 2");
        $entity2->setValue("path", "path 2");
        $entity2->setValue("website", "website 2");
        $entity2->addMultiValue("groups", 2, "note group 2");
        $dm->save($entity2);
        $entityId2 = $entity2->getId();

        $entity3 = $loader->create("note");
        $entity3->setValue("body", "body 3");
        $entity3->setValue("name", "name 3");
        $entity3->setValue("path", "path 3");
        $entity3->setValue("website", "website 3");
        $entity3->addMultiValue("groups", 3, "note group 3");
        $entity3->addMultiValue("groups", 33, "note group 33");
        $dm->save($entity3);
        $entityId3 = $entity3->getId();

        // Setup the merge data
        $data = array(
            'obj_type' => "note",
            'id' => array($entityId1, $entityId2, $entityId3),
            'merge_data' => array(
                $entityId1 => array("body"),
                $entityId2 => array("path", "website"),
                $entityId3 => array("groups", "name"),
            )
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->controller->postMergeEntitiesAction();

        // Test the results
        $this->assertFalse(empty($ret['id']));
        $this->assertEquals($ret['body'], $entity1->getValue("body"));
        $this->assertEquals($ret['path'], $entity2->getValue("path"));
        $this->assertEquals($ret['website'], $entity2->getValue("website"));
        $this->assertEquals($ret['name'], $entity3->getValue("name"));
        $this->assertEquals($ret['groups'], $entity3->getValue("groups"));
        $this->assertEquals($ret['groups_fval'][3], $entity3->getValueName("groups", 3));
        $this->assertEquals($ret['groups_fval'][33], $entity3->getValueName("groups", 33));

        // Test that the entities that were merged have been moved
        $mId1 = $dm->checkEntityHasMoved($entity1->getDefinition(), $entityId1);
        $this->assertEquals($mId1, $ret['id']);

        $mId2 = $dm->checkEntityHasMoved($entity2->getDefinition(), $entityId2);
        $this->assertEquals($mId2, $ret['id']);

        $mId3 = $dm->checkEntityHasMoved($entity3->getDefinition(), $entityId3);
        $this->assertEquals($mId3, $ret['id']);

        // Lets load the actual entities and check if they are deleted
        $originalEntity1 = $loader->get("note", $entityId1);
        $this->assertEquals($originalEntity1->getValue("f_deleted"), 1);

        $originalEntity2 = $loader->get("note", $entityId2);
        $this->assertEquals($originalEntity2->getValue("f_deleted"), 1);

        $originalEntity3 = $loader->get("note", $entityId3);
        $this->assertEquals($originalEntity3->getValue("f_deleted"), 1);
    }

    public function testSaveGroup()
    {
        // Setup the save group data
        $dataGroup = array(
            'action' => "add",
            'obj_type' => "note",
            'field_name' => 'groups',
            'name' => 'test save group',
            'color' => 'blue',
            'filter' => array('user_id' => -9)
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($dataGroup));
        $retGroup = $this->controller->postSaveGroupAction();

        $this->assertTrue($retGroup['id'] > 0);
        $this->assertEquals($retGroup['name'], $dataGroup['name']);
        $this->assertEquals($retGroup['color'], $dataGroup['color']);
        $this->assertEquals($retGroup['filter_fields']['user_id'], $dataGroup['filter']['user_id']);

        // Setup the save group data with parent
        $dataWithParent = array(
            'action' => "add",
            'obj_type' => "note",
            'field_name' => 'groups',
            'parent_id' => $retGroup['id'],
            'name' => 'test group with parent',
            'color' => 'green',
            'filter' => array('user_id' => -9)
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($dataWithParent));
        $retWithParent = $this->controller->postSaveGroupAction();

        $this->assertTrue($retWithParent['id'] > 0);
        $this->assertEquals($retWithParent['name'], $dataWithParent['name']);
        $this->assertEquals($retWithParent['color'], $dataWithParent['color']);
        $this->assertEquals($retWithParent['parent_id'], $retGroup['id']);
        $this->assertEquals($retWithParent['filter_fields']['user_id'], $dataWithParent['filter']['user_id']);

        // Test the edit function of SaveGroup
        $dataEdit = array(
            'action' => "edit",
            'obj_type' => "note",
            'field_name' => 'groups',
            'id' => $retGroup['id'],
            'name' => 'test edit group save',
            'color' => 'green',
            'filter' => array('user_id' => -9)
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($dataEdit));
        $retEdit = $this->controller->postSaveGroupAction();

        $this->assertEquals($retEdit['id'], $retGroup['id']);
        $this->assertEquals($retEdit['name'], $dataEdit['name']);
        $this->assertEquals($retEdit['color'], $dataEdit['color']);
        $this->assertEquals($retEdit['filter_fields']['user_id'], $dataEdit['filter']['user_id']);

        // Set the added groups here to be deleted later in the tearDown
        $this->testGroups = array($retWithParent['id'], $retGroup['id']);
    }

    public function testDeleteEntityDef()
    {
        $objType = "UnitTestObjType";
        $def = new EntityDefinition($objType);
        $def->setSystem(false);

        // Save the entity definition
        $dataMapper = $this->account->getServiceManager()->get(EntityDefinitionDataMapperFactory::class);
        $dataMapper->save($def);

        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $testDef = $defLoader->get($objType);
        $this->testDefinitions[] = $testDef;

        $result = $dataMapper->delete($testDef);
        $this->assertEquals($result, true);
    }
}
