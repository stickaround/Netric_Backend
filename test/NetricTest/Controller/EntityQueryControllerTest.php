<?php

/**
 * Test the entity query controller
 */
namespace NetricTest\Controller;

use Netric\Controller\EntityQueryController;
use Netric\Entity\EntityLoaderFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;

/**
 * @group integration
 */
class EntityQueryControllerTest extends TestCase
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
     * Test entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Get the service manager of the current user
        $this->serviceManager = $this->account->getServiceManager();

        // Create the controller
        $this->controller = new EntityQueryController($this->account->getApplication(), $this->account);
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
            $loader->delete($entity, true);
        }
    }

    public function testPostExecuteAction()
    {
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);
        $taskEntity = $entityLoader->create(ObjectTypes::TASK);
        $taskEntity->setValue("name", "UnitTestTask");
        $entityLoader->save($taskEntity);
        $this->testEntities[] = $taskEntity;

        // Set params in the request
        $data = ['obj_type' => ObjectTypes::TASK];
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $req->setParam('content-type', 'application/json');

        $ret = $this->controller->postExecuteAction();

        $this->assertGreaterThan(0, $ret['total_num']);
        $this->assertGreaterThan(0, $ret['num']);
        $this->assertEquals("task", $ret["entities"][0]["obj_type"]);
    }
}
