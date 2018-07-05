<?php
/**
 * Make sure the bin/scripts/update/once/004/001/024.php script works
 */
namespace BinTest\Update\Once;

use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Permissions\Dacl;
use Netric\Console\BinScript;
use Netric\EntityDefinition\EntityDefinition;
use PHPUnit\Framework\TestCase;

class Update004001025Test extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Test entity to make sure we update
     *
     * @var EntityInterface
     */
    private $testEntity = null;


    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/025.php";
        $serviceManager = $this->account->getServiceManager();
        $definitionDataMapper = $serviceManager->get(DataMapperFactory::class);

        // First cleanup
        if ($definitionDataMapper->fetchByName('utest_blankfielddata')) {
            $definitionDataMapper->deleteDef($definitionDataMapper->fetchByName('utest_blankfielddata'));
        }

        // Create a new entity type and modify the table to match pre 4.1.24
        $testType = new EntityDefinition('utest_blankfielddata');
        $testType->setTitle("Unit Test Update");
        $testType->setSystem(false);
        $testType->setDacl(new Dacl());
        $definitionDataMapper->save($testType);

        // Create test entity
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $testEntity = $entityLoader->create('utest_blankfielddata');
        $entityLoader->save($testEntity);
        $this->testEntity = $testEntity;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown()
    {
        $serviceManager = $this->account->getServiceManager();
        $definitionDataMapper = $serviceManager->get(DataMapperFactory::class);

        // delete entities
        if ($this->testEntity) {
            $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
            $entityLoader->delete($this->testEntity, true);
        }

        // cleanup definition
        if ($definitionDataMapper->fetchByName('utest_blankfielddata')) {
            $definitionDataMapper->deleteDef($definitionDataMapper->fetchByName('utest_blankfielddata'));
        }
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right, but why
     * not just use unit tests for our tests? :)
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $db = $serviceManager->get(RelationalDbFactory::class);

        // Modify the raw data and set field_data to null to invoke the update script
        $db->query(
            'UPDATE objects SET field_data=NULL WHERE guid=:guid',
            $this->testEntity->getValue('guid')
        );

        // Run the 025.php update once script to scan the objects_moved table and update the referenced entities
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Make sure a guid was set (clear cache first then reload fresh)
        $result = $db->query(
            'SELECT field_data FROM objects WHERE guid=:guid',
            $this->testEntity->getValue('guid')
        );
        $row = $result->fetchRow();
        $this->assertNotEmpty($row['field_data']);
    }
}