<?php

/**
 * Test the module controller
 */

namespace NetricTest\Controller;

use Netric\Controller\ModuleController;
use Netric\Account\Module\ModuleServiceFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class ModuleControllerTest extends TestCase
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
     * Test modules that should be cleaned up on tearDown
     *
     * @var ModuleInterface[]
     */
    private $testModules = [];

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Get the service manager of the current user
        $this->serviceManager = $this->account->getServiceManager();

        // Create the controller
        $this->controller = new ModuleController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
    {
        // Cleanup test modules
        $moduleService = $this->serviceManager->get(ModuleServiceFactory::class);
        foreach ($this->testModules as $module) {
            $moduleService->delete($module);
        }
    }

    public function testGetGetAction()
    {
        $moduleService = $this->serviceManager->get(ModuleServiceFactory::class);
        $module = $moduleService->createNewModule();
        $module->setName("unit_test_module_get");
        $module->setTitle("Unit Test Module");
        $moduleService->save($module);
        $this->testModules[] = $module;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('moduleName', 'unit_test_module_get');

        $ret = $this->controller->getGetAction();
        $this->assertEquals($module->getModuleId(), $ret['id'], var_export($ret, true));
    }

    public function testSaveAction()
    {
        $data = [
            'name' => "unit_test_module",
            'title' => "Unit Test Module",
        ];

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->controller->postSaveAction();

        $moduleService = $this->serviceManager->get(ModuleServiceFactory::class);
        $module = $moduleService->createNewModule();
        $module->fromArray($ret);
        $this->testModules[] = $module;

        $this->assertGreaterThan(0, $module->getModuleId());
        $this->assertEquals($data['name'], $module->getName(), var_export($ret, true));
        $this->assertEquals($data['title'], $module->getTitle(), var_export($ret, true));
    }

    public function testDeleteAction()
    {
        $moduleService = $this->serviceManager->get(ModuleServiceFactory::class);
        $module = $moduleService->createNewModule();
        $module->setName("unit_test_module_delete");
        $module->setTitle("Unit Test Module");
        $moduleService->save($module);

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam('id', $module->getModuleId());
        $ret = $this->controller->postDeleteAction();

        $this->assertTrue($ret, var_export($module->toArray(), true));
    }

    public function testGetGetAvailableModulesAction()
    {
        $userId = $this->account->getUser()->getEntityId();

        $moduleService = $this->serviceManager->get(ModuleServiceFactory::class);
        $module = $moduleService->createNewModule();
        $module->setName("unit_test_module_available");
        $module->setTitle("Unit Test Module");
        $module->setUserId($userId);
        $moduleService->save($module);
        $this->testModules[] = $module;

        $ret = $this->controller->getGetAvailableModulesAction();
        $this->assertGreaterThan(0, sizeof($ret), var_export($ret, true));
        $this->assertGreaterThan(0, $ret[0]["id"], var_export($ret, true));
    }
}
