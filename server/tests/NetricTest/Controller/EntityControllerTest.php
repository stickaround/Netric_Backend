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

        // Setup a user for testing
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $user = $loader->get("user", \Netric\Entity\ObjType\UserEntity::USER_ADMINISTRATOR);
        $this->account->setCurrentUser($user);

        // Create the controller
        $this->controller = new Netric\Controller\EntityController($this->account);
        $this->controller->testMode = true;
    }

    /*public function testGetDefinitionForms()
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

    public function getAllDefinitionsAction()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $ret = $this->controller->getGetDefinitionAction();

        $this->assertTrue($ret[0]['id'] > 0);

        // Make sure the small form was loaded
        $this->assertFalse(empty($ret[0]['forms']['small']));

        // Make sure the large form was loaded
        $this->assertFalse(empty($ret[0]['forms']['large']));
    }*/

    public function testGetAction()
    {
        $data = array(
            'obj_type' => "html_template",
            'id' => "7",
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('obj_type', "html_template");
        $req->setParam('id', "7");

        $ret = $this->controller->getGetAction();
    }
}