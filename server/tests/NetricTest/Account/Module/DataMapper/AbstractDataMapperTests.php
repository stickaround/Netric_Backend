<?php
/**
 * Abstract tests for CRUD on a Module
 */
namespace NetricTest\Account\Module\DataMapper;

use Netric\Account\Module\DataMapper;
use Netric\Account\Module\Module;
use PHPUnit_Framework_TestCase;

abstract class AbstractDataMapperTests extends PHPUnit_Framework_TestCase
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
    protected function tearDown()
    {
        $dataMapper = $this->getDataMapper();
        foreach ($this->testModules as $module)
        {
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
        $this->assertNotEmpty($module->getId(), $dataMapper->getLastError());
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
        $this->assertTrue($dataMapper->save($module), $dataMapper->getLastError());

        // Re-open and check
        $module2 = $dataMapper->get($module->getName());
        $this->assertEquals($module->toArray(), $module2->toArray());
    }

    public function testGet()
    {
        $dataMapper = $this->getDataMapper();

        // Get a system module that will always exist
        $module = $dataMapper->get("notes");
        $this->assertNotNull($module);
        $this->assertNotEmpty($module->getId());
    }

    public function testGetAll()
    {
        $dataMapper = $this->getDataMapper();
        $modules = $dataMapper->getAll();
        $this->assertNotNull($modules);
        $this->assertGreaterThan(0, count($modules), $dataMapper->getLastError());
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