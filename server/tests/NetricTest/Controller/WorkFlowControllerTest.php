<?php
/**
 * Make sure we can interact with WorkFlows through the API
 */
namespace NetricTest\Controller;

use Netric;
use Netric\Entity\ObjType\UserEntity;
use Netric\Controller\WorkflowController;
use PHPUnit_Framework_TestCase;
use Netric\EntityQuery\Where;
use Netric\WorkFlow\WorkFlow;
use Netric\WorkFlow\Action\ActionFactory;

class WorkflowControllerTest extends PHPUnit_Framework_TestCase
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

    /**
     * Setup the controller for test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Setup a user for testing
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $user = $loader->get("user", UserEntity::USER_ADMINISTRATOR);
        $this->account->setCurrentUser($user);

        // Create the controller
        $this->controller = new WorkflowController($this->account);
        $this->controller->testMode = true;
    }

    public function testGetWorkflowsAction()
    {
        $workFlows = $this->controller->getWorkflowsAction();
        $this->assertFalse(array_key_exists("error", $workFlows));
        $this->assertGreaterThan(0, count($workFlows));
    }

    public function testGetWorkflowAction()
    {
        $serviceManager = $this->account->getServiceManager();
        $dataMapper = $serviceManager->get("Netric/WorkFlow/DataMapper/DataMapper");

        // Data to save and test
        $workFlowData = array(
            "name" => "Test Save",
            "obj_type" => "task",
            "notes" => "Details Here",
            "active" => true,
            "on_create" => true,
        );

        // Create and save the workflow
        $actionFactory = new ActionFactory($serviceManager);
        $workFlow = new WorkFlow($actionFactory);
        $workFlow->fromArray($workFlowData);
        $workflowId = $dataMapper->save($workFlow);
        $this->testWorkFlows[] = $workFlow;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('id', $workflowId);

        // Get workflow from the controller
        $resp = $this->controller->getWorkflowAction();
        $this->assertNotNull($resp['id']);
        $this->assertEquals($workFlowData['name'], $resp['name']);
        $this->assertEquals($workFlowData['obj_type'], $resp['obj_type']);
        $this->assertEquals($workFlowData['notes'], $resp['notes']);

        // Cleanup
        $dataMapper->delete($workFlow);
    }

    public function testPostSaveWorkflowAction()
    {
        // Data to save and test
        $workFlowData = array(
            "name" => "Test Save",
            "obj_type" => "task",
            "notes" => "Details Here",
            "active" => true,
            "on_create" => true,
            "on_update" => true,
            "on_delete" => true,
            "singleton" => false,
            "allow_manual" => false,
            "only_on_conditions_unmet" => true,
            "conditions" => array(
                array(
                    "blogic" => Where::COMBINED_BY_AND,
                    "field_name" => "done",
                    "operator" => Where::OPERATOR_EQUAL_TO,
                    "value" => true,
                )
            ),
            "actions" => array(
                array(
                    "name" => "my action",
                    "type" => "test",
                ),
            ),
        );

        // Set the raw body
        $req = $this->controller->getRequest();
        $req->setParam('raw_body', json_encode($workFlowData));

        // Run the action
        $ret = $this->controller->postSaveAction();

        // Make sure we saved it successfully
        $this->assertArrayHasKey('id', $ret);
        $this->assertNotNull($ret['id']);

        // Cleanup
        $dm = $this->account->getServiceManager()->get("Netric/WorkFlow/DataMapper/DataMapper");
        $workFlow = $dm->getById($ret['id']);
        $dm->delete($workFlow);
    }
}