<?php
/**
 * Test the entity controller
 */
namespace NetricTest\Controller;

use Netric;
use PHPUnit_Framework_TestCase;

class EntityControllerTest extends PHPUnit_Framework_TestCase
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
     * @var \Netric\Controller\EntityController
     */
    protected $controller = null;

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Create the controller
        $this->controller = new Netric\Controller\EntityController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;
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
        $data = array(
            'obj_type' => "customer",
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
        $this->assertTrue($ret['fields']['test_field']['id'] > 0);

        // Remove the custom test field added
        $data = array(
            'obj_type' => "customer",
            'deleted_fields' => array("test_field")
        );

        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->controller->postUpdateEntityDefAction();

        $this->assertArrayNotHasKey('test_field', $ret['fields']);
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


        // Lets load the actual actual entities and test them
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

    /**
     * POST pass-through for merge entities
     */
    public function postMergeEntitiesAction()
    {
        return $this->getMergeEntitiesAction();
    }

    /**
     * Function that will handle the merging of entities
     *
     * @return {array} Returns the array of updated entities
     */
    public function getMergeEntitiesAction()
    {
        $rawBody = $this->getRequest()->getBody();

        if (!$rawBody)
        {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        // Check if we have obj_type. If it is not defined, then return an error
        if (!isset($objData['obj_type']))
        {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        // Check if we have entity_data. If it is not defined, then return an error
        if (!isset($objData['merge_data']))
        {
            return $this->sendOutput(array("error" => "merge_data is a required param"));
        }

        $mergeData = $objData['merge_data'];

        // Get the entity loader so we can initialize (and check the permissions for) each entity
        $loader = $this->account->getServiceManager()->get("Netric/EntityLoader");

        // Get the datamapper
        $dataMapper = $this->account->getServiceManager()->get("Netric/Entity/DataMapper/DataMapper");

        // Create the new entity where we merge all field values
        $mergedEntity = $loader->create($objData['obj_type']);

        $entityData = array();

        foreach ($mergeData as $entityId => $fields)
        {
            $entity = $loader->get($objData['obj_type'], $entityId);

            // Build the entity data and get the field values from the entity we want to merge
            foreach ($fields as $field)
            {
                $fieldValue = $entity->getValue($field);
                $entityData[$field] = $fieldValue;

                // Let's check if the field value is an array, then we need to get its value names
                if(is_array($fieldValue))
                {
                    $entityData["{$field}_fval"] = $entity->getValueNames($field);
                }
            }

            // Let's delete the entity after getting the data that will be used in the merge
            $dataMapper->delete($entity);
        }

        // Set the fields with the merged data.
        $mergedEntity->fromArray($entityData, true);

        // Now save the the entity where all merged data are set
        $dataMapper->save($mergedEntity);

        // Return the merged entity
        return $this->sendOutput($mergedEntity->toArray());
    }
}