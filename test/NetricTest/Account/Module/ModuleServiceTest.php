<?php

/**
 * Make sure the module service works
 */

namespace NetricTest\Account\Module;

use Netric\Account\Module\Module;
use Netric\Account\Module\ModuleService;
use PHPUnit\Framework\TestCase;
use Netric\Account\Module\ModuleServiceFactory;
use NetricTest\Bootstrap;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * @group integration
 */
class ModuleServiceTest extends TestCase
{
    /**
     * Module service instance to test
     *
     * @var ModuleService
     */
    private $moduleService = null;

    /**
     * Temp or test modules to cleanup on tearDown
     *
     * @var Module[]
     */
    protected $testModules = [];

    protected function setUp(): void
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->moduleService = $sm->get(ModuleServiceFactory::class);
    }

    /**
     * Cleanup any created assets
     */
    protected function tearDown(): void
    {
        foreach ($this->testModules as $module) {
            $this->moduleService->delete($module);
        }
    }

    public function testSave()
    {
        $module = new Module();
        $module->setName("test-" . rand());
        $module->setSystem(false);
        $module->setTitle("Unit Test Module");
        $module->setShortTitle("Test");
        $module->setScope(Module::SCOPE_EVERYONE);

        // Save it
        $this->assertTrue($this->moduleService->save($module));
        $this->testModules[] = $module;
    }

    public function testDelete()
    {
        // Save first
        $module = new Module();
        $module->setName("test-" . rand());
        $module->setSystem(false);
        $module->setTitle("Unit Test Module");
        $module->setShortTitle("Test");
        $module->setScope(Module::SCOPE_EVERYONE);

        // Save it initially
        $this->moduleService->save($module);

        // Delete it
        $this->assertTrue($this->moduleService->delete($module));
    }

    public function testGetByName()
    {
        // Get a system module that will always exist
        $module = $this->moduleService->getByName("knowledge");
        $this->assertNotNull($module);
        $this->assertNotEmpty($module->getModuleId());
    }

    public function testGetById()
    {
        // First get by name
        $module = $this->moduleService->getByName("knowledge");
        $this->assertNotNull($module);
        $this->assertNotEmpty($module->getModuleId());

        // Now try to get by id
        $module2 = $this->moduleService->getById($module->getModuleId());
        $this->assertEquals($module->getModuleId(), $module2->getModuleId());
    }

    public function testGetForUser()
    {
        // Create a temp user
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $entityLoader = $sm->get(EntityLoaderFactory::class);
        $user = $entityLoader->create(ObjectTypes::USER, $account->getAccountId());

        // Make sure we can get modules for this entity
        $modules = $this->moduleService->getForUser($user);
        $this->assertGreaterThan(0, count($modules));
    }

    public function testCreateNewModule()
    {
        $module = $this->moduleService->createNewModule();
        $this->assertEquals($module->getModuleId(), null);
    }
}
