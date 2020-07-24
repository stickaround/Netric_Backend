<?php

/**
 * Abstract tests for CRUD on a Module
 */

namespace NetricTest\Account\Module\DataMapper;

use Netric\Account\Module\DataMapper;
use Netric\Account\Module\Module;
use PHPUnit\Framework\TestCase;

abstract class AbstractDataMapperTests extends TestCase
{
    /**
     * Temp or test modules to cleanup on tearDown
     *
     * @var Module[]
     */
    protected $testModules = [];

    /**
     * Required by all DataMapper tests to construct implementation of DataMapper
     *
     * @return DataMapper\DataMapperInterface
     */
    abstract public function getDataMapper();

    /**
     * Cleanup any created assets
     */
    protected function tearDown(): void
    {
        $dataMapper = $this->getDataMapper();
        foreach ($this->testModules as $module) {
            $dataMapper->delete($module);
        }
    }

    public function testSave_create()
    {
        $dataMapper = $this->getDataMapper();

        $module = new Module();
        $module->setName("test-" . rand());
        $module->setSystem(false);
        $module->setTitle("Unit Test Module");
        $module->setShortTitle("Test");
        $module->setScope(Module::SCOPE_EVERYONE);

        // Save it
        $dataMapper->save($module);
        $this->assertNotEmpty($module->getModuleId());
        $this->testModules[] = $module; // For cleanup

        // Re-open and check
        $module2 = $dataMapper->get($module->getName());
        $this->assertEquals($module->toArray(), $module2->toArray());
    }

    public function testSave_update()
    {
        $dataMapper = $this->getDataMapper();

        // Save first
        $module = new Module();
        $module->setName("test-" . rand());
        $module->setSystem(false);
        $module->setTitle("Unit Test Module");
        $module->setShortTitle("Test");
        $module->setScope(Module::SCOPE_EVERYONE);

        // Save it initially
        $dataMapper->save($module);
        $this->testModules[] = $module; // For cleanup

        // Save changes
        $module->setTitle("Unit Test Module - edited");
        $this->assertTrue($dataMapper->save($module));

        // Re-open and check
        $module2 = $dataMapper->get($module->getName());
        $this->assertEquals($module->toArray(), $module2->toArray());
    }

    public function testGet()
    {
        $dataMapper = $this->getDataMapper();

        // Get a system module that will always exist
        $module = $dataMapper->get("home");
        $this->assertNotNull($module);
        $this->assertNotEmpty($module->getModuleId());

        // Make sure that the navigation set is an array
        $this->assertTrue(is_array($module->getNavigation()));
    }

    public function testGetAll()
    {
        $dataMapper = $this->getDataMapper();
        $modules = $dataMapper->getAll();
        $this->assertNotNull($modules);
        $this->assertGreaterThan(0, count($modules));
    }

    public function testSaving()
    {
        $dataMapper = $this->getDataMapper();

        // Get a system module that will be tested for saving
        $module = $dataMapper->get("home");

        // Update the short title
        $module->setShortTitle("Personal Home");
        $dataMapper->save($module);

        // It should only update the short title and not the home
        $newModule = $dataMapper->get("home");

        $this->assertEquals($newModule->getShortTitle(), "Personal Home");
        $this->assertEquals($newModule->getNavigation(), $module->getNavigation());

        // Reset back the Home short title
        $module->setShortTitle("Home");
        $dataMapper->save($module);
    }

    public function testNavigationSaving()
    {
        $dataMapper = $this->getDataMapper();

        // Get a system module that will be tested for saving
        $module = $dataMapper->get("home");

        // Updat the navigation with new data
        $nav = [
            [
                "title" => "New Note",
                "type" => "entity",
                "route" => "new-note",
                "objType" => "note",
                "icon" => "plus",
            ]
        ];
        $module->setNavigation($nav);

        // Save the updated navigation
        $dataMapper->save($module);

        // It should update the navigation
        $newModule = $dataMapper->get("home");
        $newNav = $newModule->getNavigation();
        $this->assertEquals($newNav[0]['route'], $nav[0]['route']);
    }

    public function testDelete()
    {
        $dataMapper = $this->getDataMapper();

        // Save first
        $module = new Module();
        $module->setName("test-" . rand());
        $module->setSystem(false);
        $module->setTitle("Unit Test Module");
        $module->setShortTitle("Test");
        $module->setScope(Module::SCOPE_EVERYONE);

        // Save it initially
        $dataMapper->save($module);

        // Delete it
        $dataMapper->delete($module);

        // Make sure we cannot open it
        $this->assertNull($dataMapper->get($module->getName()));
    }
}
