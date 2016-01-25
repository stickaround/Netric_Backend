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
        $req->setParam('raw_body', json_encode($data));

        $ret = $this->controller->postSaveAction();

        $this->assertEquals($data['obj_type'], $ret['obj_type']);
        $this->assertEquals($data['first_name'], $ret['first_name']);
        $this->assertEquals($data['last_name'], $ret['last_name']);
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
}