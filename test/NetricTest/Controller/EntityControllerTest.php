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
use Netric\Entity\DataMapper\DataMapperFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityGroupings\Group;

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

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Create the controller
        $this->controller = new EntityController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
    {
        // Delete the added groups
        foreach ($this->testGroups as $groupId) {
            $dataRemove = [
                'action' => "delete",
                'obj_type' => ObjectTypes::NOTE,
                'field_name' => 'groups',
                'id' => $groupId
            ];

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
        $dashboardEntity = $loader->create(ObjectTypes::DASHBOARD);
        $dashboardEntity->setValue("name", "activity-new");
        $loader->save($dashboardEntity);
        $this->testEntities[] = $dashboardEntity;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode(array(
            'obj_type' => ObjectTypes::DASHBOARD,
            'guid' => $dashboardEntity->getEntityId()
        )));

        $ret = $this->controller->getGetAction();
        $this->assertEquals($dashboardEntity->getEntityId(), $ret['guid'], var_export($ret, true));
    }

    public function testPostGetEntityActionDashboardUname()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dashboardEntity = $loader->create(ObjectTypes::DASHBOARD);
        $dashboardName =  "activity-test" . uniqid();
        $dashboardEntity->setValue("name", $dashboardName);
        $dashboardEntity->setValue("owner_id", $this->account->getUser()->getEntityId());
        $loader->save($dashboardEntity);
        $this->testEntities[] = $dashboardEntity;

        // Test The getting of entity using unique name
        // Set params in the request
        $data = array(
            'obj_type' => ObjectTypes::DASHBOARD,
            'uname' => $dashboardEntity->getValue("uname"),
            'uname_conditions' => [
                'owner_id' => $this->account->getUser()->getEntityId(),
            ],
        );
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postGetAction();
        $dashboardEntity = $loader->getByGuid($ret['guid']);
        $this->assertEquals($dashboardEntity->getValue("name"), $dashboardName);
    }

    public function testPostGetEntityAction()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $customer = $loader->create(ObjectTypes::CONTACT);
        $customer->setValue("name", "Test");
        $loader->save($customer);
        $this->testEntities[] = $customer;

        $data = array(
            'obj_type' => ObjectTypes::CONTACT,
            'guid' => $customer->getEntityId(),
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postGetAction();
        $this->assertEquals($customer->getEntityId(), $ret['guid'], var_export($ret, true));
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
        $page->setValue("site_id", $site->getEntityId());
        $loader->save($page);
        $this->testEntities[] = $page;

        $data = array(
            'obj_type' => "cms_page",
            'uname' => $page->getValue("uname"),
            'uname_conditions' => [
                'site_id' => $site->getEntityId(),
            ],
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postGetAction();
        $this->assertEquals($page->getEntityId(), $ret['guid'], var_export($ret, true));
    }

    /**
     * Test getting an entity by guid
     */
    public function testPostGetEntityActionGuid()
    {
        // Create a test entity for querying
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $site = $loader->create("cms_site");
        $site->setValue("name", "www.testsite.com");
        $loader->save($site);
        $this->testEntities[] = $site;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode(['entity_id' => $site->getEntityId]));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postGetAction();
        $this->assertEquals($site->getEntityId, $ret['guid'], var_export($ret, true));
    }

    public function testGetDefinitionForms()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', ObjectTypes::CONTACT);

        $ret = $this->controller->getGetDefinitionAction();

        // Make sure the small form was loaded
        $this->assertFalse(empty($ret['forms']['small']));

        // Make sure the large form was loaded
        $this->assertFalse(empty($ret['forms']['large']));
    }

    public function testSave()
    {
        $data = array(
            'obj_type' => ObjectTypes::CONTACT,
            'first_name' => "Test",
            'last_name' => "User",
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->controller->postSaveAction();

        $this->assertEquals($data['obj_type'], $ret['obj_type']);
        $this->assertEquals($data['first_name'], $ret['first_name']);
        $this->assertEquals($data['last_name'], $ret['last_name']);
    }

    public function testDelete()
    {
        // First create an entity to save
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $entity = $loader->create(ObjectTypes::NOTE);
        $entity->setValue("name", "Test");
        $dm = $this->account->getServiceManager()->get(DataMapperFactory::class);
        $dm->save($entity);
        $entityId = $entity->getEntityId();

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("obj_type", ObjectTypes::NOTE);
        $req->setParam("id", $entityId);

        // Try to delete
        $ret = $this->controller->postRemoveAction();
        $this->assertEquals($entityId, $ret[0]);
    }

    public function testGetGroupings()
    {
        $req = $this->controller->getRequest();
        $req->setParam("obj_type", ObjectTypes::CONTACT);
        $req->setParam("field_name", "groups");

        $ret = $this->controller->getGetGroupingsAction();
        $this->assertFalse(isset($ret['error']));
        $this->assertTrue(count($ret) > 0);
    }

    public function testGetAllDefinitionsAction()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $ret = $this->controller->getAllDefinitionsAction();

        // Try to get the task entity definition and we use it in our unit test
        $entityDefData = null;
        foreach ($ret as $defData) {
            if ($defData['obj_type'] === "task") {
                $entityDefData = $defData;
                break;
            }
        }

        $this->assertNotNull($entityDefData);
        $this->assertTrue($entityDefData['id'] > 0);

        // Make sure the small form was loaded
        $this->assertFalse(empty($entityDefData['forms']['small']));

        // Make sure the large form was loaded
        $this->assertFalse(empty($entityDefData['forms']['large']));
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

    public function testPostDeleteEntityDefAction()
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

        // Now Delete the newly created entity definition
        $data = array(
            'obj_type' => $objType,
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->controller->postDeleteEntityDefAction();
        $this->assertTrue($ret);
    }

    public function testMassEdit()
    {
        // Setup the loaders
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dm = $this->account->getServiceManager()->get(DataMapperFactory::class);
        $groupingsLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);

        $groupings = $groupingsLoader->get(ObjectTypes::NOTE . "/groups/" . $this->account->getUser()->getEntityId());

        $group1 = new Group();
        $group2 = new Group();
        $group1->setValue("name", "group1");
        $group2->setValue("name", "group2");
        $groupings->add($group1);
        $groupings->add($group2);
        $groupingsLoader->save($groupings);

        $this->testGroups[] = $group1->id;
        $this->testGroups[] = $group2->id;

        // First create entities to save
        $entity1 = $loader->create(ObjectTypes::NOTE);
        $entity1->setValue("body", "Note 1");
        $entity1->addMultiValue("groups", $group1->guid, $group1->name);
        $dm->save($entity1);
        $entityGuid1 = $entity1->getEntityId();
        $this->testEntities[] = $entity1;

        $entity2 = $loader->create(ObjectTypes::NOTE);
        $entity2->setValue("body", "Note 2");
        $entity2->addMultiValue("groups", $group2->guid, $group2->name);
        $dm->save($entity2);
        $entityGuid2 = $entity2->getEntityId();
        $this->testEntities[] = $entity2;

        $groupData[$group1->guid] = $group1->name;
        $groupData[$group2->guid] = $group2->name;

        // Setup the data
        $data = array(
            'guid' => array($entityGuid1, $entityGuid2, "invalid-guid"),
            'entity_data' => array(
                "body" => "test mass edit",
                "groups" => array($group1->guid, $group2->guid),
                "groups_fval" => $groupData
            )
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));

        $ret = $this->controller->postMassEditAction();

        // Test the results
        $this->assertEquals(sizeof($ret), 3);
        $this->assertEquals($ret["error"][0], "Invalid guid was provided during mass edit action. Guid: invalid-guid.");
        $this->assertEquals($data['entity_data']['body'], $ret[0]['body']);
        $this->assertEquals($data['entity_data']['body'], $ret[1]['body']);

        // TODO: Need to redo all the unit tests for mass edit regarding groups since we already moved to use the guid for groups
    }

    public function testMergeEntities()
    {
        // Setup the loaders
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dm = $this->account->getServiceManager()->get(DataMapperFactory::class);

        // First create entities to merge
        $entity1 = $loader->create(ObjectTypes::NOTE);
        $entity1->setValue("body", "body 1");
        $entity1->setValue("name", "name 1");
        $entity1->setValue("title", "title 1");
        $entity1->setValue("website", "website 1");
        $entity1->addMultiValue("groups", 1, "note group 1");
        $dm->save($entity1);
        $entityId1 = $entity1->getEntityId();

        $entity2 = $loader->create(ObjectTypes::NOTE);
        $entity2->setValue("body", "body 2");
        $entity2->setValue("name", "name 2");
        $entity2->setValue("path", "path 2");
        $entity2->setValue("website", "website 2");
        $entity2->addMultiValue("groups", 2, "note group 2");
        $dm->save($entity2);
        $entityId2 = $entity2->getEntityId();

        $entity3 = $loader->create(ObjectTypes::NOTE);
        $entity3->setValue("body", "body 3");
        $entity3->setValue("name", "name 3");
        $entity3->setValue("path", "path 3");
        $entity3->setValue("website", "website 3");
        $entity3->addMultiValue("groups", 3, "note group 3");
        $entity3->addMultiValue("groups", 33, "note group 33");
        $dm->save($entity3);
        $entityId3 = $entity3->getEntityId();

        // Setup the merge data
        $data = array(
            'obj_type' => ObjectTypes::NOTE,
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
        $originalEntity1 = $loader->get(ObjectTypes::NOTE, $entityId1);
        $this->assertEquals($originalEntity1->getValue("f_deleted"), 1);

        $originalEntity2 = $loader->get(ObjectTypes::NOTE, $entityId2);
        $this->assertEquals($originalEntity2->getValue("f_deleted"), 1);

        $originalEntity3 = $loader->get(ObjectTypes::NOTE, $entityId3);
        $this->assertEquals($originalEntity3->getValue("f_deleted"), 1);
    }

    public function testSaveGroup()
    {
        // Setup the save group data
        $dataGroup = array(
            'action' => "add",
            'obj_type' => ObjectTypes::NOTE,
            'field_name' => 'groups',
            'name' => 'test save group',
            'color' => 'blue'
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($dataGroup));
        $retGroup = $this->controller->postSaveGroupAction();

        $this->assertTrue($retGroup['id'] > 0);
        $this->assertEquals($retGroup['name'], $dataGroup['name']);
        $this->assertEquals($retGroup['color'], $dataGroup['color']);

        // Setup the save group data with parent
        $dataWithParent = array(
            'action' => "add",
            'obj_type' => ObjectTypes::NOTE,
            'field_name' => 'groups',
            'parent_id' => $retGroup['id'],
            'name' => 'test group with parent',
            'color' => 'green'
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($dataWithParent));
        $retWithParent = $this->controller->postSaveGroupAction();

        $this->assertTrue($retWithParent['id'] > 0);
        $this->assertEquals($retWithParent['name'], $dataWithParent['name']);
        $this->assertEquals($retWithParent['color'], $dataWithParent['color']);
        $this->assertEquals($retWithParent['parent_id'], $retGroup['id']);

        // Test the edit function of SaveGroup
        $dataEdit = array(
            'action' => "edit",
            'obj_type' => ObjectTypes::NOTE,
            'field_name' => 'groups',
            'id' => $retGroup['id'],
            'name' => 'test edit group save',
            'color' => 'green'
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($dataEdit));
        $retEdit = $this->controller->postSaveGroupAction();

        $this->assertEquals($retEdit['id'], $retGroup['id']);
        $this->assertEquals($retEdit['name'], $dataEdit['name']);
        $this->assertEquals($retEdit['color'], $dataEdit['color']);

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

    public function testGetDefinitionActionToReturnError()
    {
        // Set obj_type that is currently not existing
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', 'NonExistingDefinition');
        $ret = $this->controller->getGetDefinitionAction();

        // Test that error was being returned
        $this->assertNotEmpty($ret['error']);
    }

    public function testGetActionToReturnError()
    {
        // Calling getGetAction without any parameters should return an empty array 
        $ret = $this->controller->getGetAction();
        $this->assertEquals($ret, []);

        $req = $this->controller->getRequest();

        // Setting an empty guid should return an error
        $req->setBody(json_encode(array('guid' => '')));
        $ret = $this->controller->getGetAction();
        $this->assertEquals($ret['error'], 'guid, or obj_type + id, or uname are required params.');

        // Setting a object type only should return an error
        $req->setBody(json_encode(array('obj_type' => ObjectTypes::TASK)));
        $ret = $this->controller->getGetAction();
        $this->assertEquals($ret['error'], 'guid, or obj_type + id, or uname are required params.');

        // Setting entity id only should return an error
        $req->setBody(json_encode(array('id' => 1)));
        $ret = $this->controller->getGetAction();
        $this->assertEquals($ret['error'], 'guid, or obj_type + id, or uname are required params.');

        // Setting empty uname should return an error
        $req->setBody(json_encode(array('uname' => '')));
        $ret = $this->controller->getGetAction();
        $this->assertEquals($ret['error'], 'guid, or obj_type + id, or uname are required params.');

        // Setting an alpha numeric id when calling getGetAction and it should return an error
        $req->setBody(json_encode(array('obj_type' => ObjectTypes::TASK, 'id' => 'invalidId123')));
        $ret = $this->controller->getGetAction();
        $this->assertEquals($ret['error'], 'invalidId123 is not a valid entity id');
    }

    public function testPostDeleteEntityDefActionToReturnError()
    {
        // Deleting an entity definition without providing an object type should return an error
        $req = $this->controller->getRequest();
        $req->setBody(json_encode(array('name' => 'DefWithNoType')));
        $ret = $this->controller->postDeleteEntityDefAction();
        $this->assertEquals($ret['error'], 'obj_type is a required param');
    }

    public function testPostUpdateEntityDefActionToReturnError()
    {
        $req = $this->controller->getRequest();

        // Saving an entity definition without providing an object type should return an error
        $req->setBody(json_encode(array('name' => 'DefWithNoType')));
        $ret = $this->controller->postUpdateEntityDefAction();
        $this->assertEquals($ret['error'], 'obj_type is a required param');

        // Saving an entity definition with an empty obj_type should return an error
        $req->setBody(json_encode(array('obj_type' => '', 'name' => 'DefWithNoType')));
        $ret = $this->controller->postUpdateEntityDefAction();
        $this->assertEquals($ret['error'], 'obj_type is empty.');

        // Saving an existing entity definition but with non existing object type
        $req->setBody(json_encode(array('obj_type' => 'NonExistingDefinition', 'id' => 1)));
        $ret = $this->controller->postUpdateEntityDefAction();
        $this->assertNotEmpty($ret['error']);
    }

    public function testPostSaveActionToReturnError()
    {
        $req = $this->controller->getRequest();

        // Saving an entity without providing any data should return an error
        $ret = $this->controller->putSaveAction();
        $this->assertNotEmpty($ret['error']);

        // Saving an entity with invalid id should return an error
        $req->setBody(json_encode(array('obj_type' => ObjectTypes::TASK, 'id' => 'invalidId123')));
        $ret = $this->controller->postSaveAction();
        $this->assertEquals($ret['error'], 'invalidId123 is not a valid entity id');
    }

    public function testGetRemoveActionToReturnError()
    {
        $req = $this->controller->getRequest();

        // Removing an entity with an empty object type should return an error
        $req->setParam('obj_type', '');
        $ret = $this->controller->getRemoveAction();
        $this->assertEquals($ret['error'], 'obj_type is a required param');
    }

    public function testPostSaveGroupActionToReturnError()
    {
        $req = $this->controller->getRequest();

        // Saving a group without object type should return an error
        $req->setBody(json_encode(array('field_name' => 'group')));
        $ret = $this->controller->postSaveGroupAction();
        $this->assertEquals($ret['error'], 'obj_type is a required param');

        // Saving a group without field name should return an error
        $req->setBody(json_encode(array('obj_type' => ObjectTypes::TASK)));
        $ret = $this->controller->postSaveGroupAction();
        $this->assertEquals($ret['error'], 'field_name is a required param');

        // Saving a group without an action should return an error
        $req->setBody(json_encode(array('obj_type' => ObjectTypes::TASK, 'field_name' => 'group')));
        $ret = $this->controller->postSaveGroupAction();
        $this->assertEquals($ret['error'], 'action is a required param');
    }
}
